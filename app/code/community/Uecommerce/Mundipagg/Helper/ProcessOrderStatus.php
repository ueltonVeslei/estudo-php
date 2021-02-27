<?php

/**
 * Class Uecommerce_Mundipagg_Helper_ProcessOrderStatus
 * Deal with order status.
 */
class Uecommerce_Mundipagg_Helper_ProcessOrderStatus extends Mage_Core_Helper_Abstract
{
    const TRANSACTION_CAPTURED = "Transaction captured";

    public function captured($order, $amountToCapture, $transactionKey, $orderReference, $amountInCents = null)
    {
        $log = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
        $transactionHelper = Mage::helper('mundipagg/transaction');
        
        try {
            $return = $transactionHelper->captureTransaction($order, $amountToCapture, $transactionKey, $amountInCents);
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            $returnMessage = "OK | #{$orderReference} | {$transactionKey} | ";
            $returnMessage .= "Can't capture transaction: {$errMsg}";
            $log->info($returnMessage);
            $log->info("Current order status: " . $order->getStatusLabel());
            return $returnMessage;
        }
        if ($return instanceof Mage_Sales_Model_Order_Invoice) {
            Mage::helper('mundipagg')->sendNewInvoiceEmail($return,$order);

            $returnMessage = "OK | #{$orderReference} | {$transactionKey} | Invoice created - " . self::TRANSACTION_CAPTURED;
            $log->info($returnMessage);
            $log->info("#{$orderReference} | Invoice Created - Current order status: " . $order->getStatusLabel());
            return $returnMessage;
        }
        if ($return === self::TRANSACTION_CAPTURED) {
            $returnMessage = "OK | #{$orderReference} | {$transactionKey} | Transaction captured, but there are some remaining.";
            $log->info($returnMessage);
            $log->info("#{$orderReference} | Current order status: " . $order->getStatusLabel());
            return $returnMessage;
        }
        // cannot capture transaction
        $returnMessage = "KO | #{$orderReference} | {$transactionKey} | Transaction can't be captured: ";
        $returnMessage .= $return;
        $log->info($returnMessage);
        $log->info("#{$orderReference} | Current order status: " . $order->getStatusLabel());
        return $returnMessage;
    }

    public function paidOverpaid($order,$returnMessageLabel,$capturedAmountInCents,$data,$status)
    {
        $helperLog = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
        $orderReference = $order->getIncrementId();

        if ($order->canUnhold()) {
            $order->unhold();
            $helperLog->info("{$returnMessageLabel} | unholded.");
            $helperLog->info("Current order status: " . $order->getStatusLabel());
        }
        if (!$order->canInvoice()) {
            $returnMessage = "OK | {$returnMessageLabel} | Can't create invoice. Transaction status '{$status}' processed.";
            $helperLog->info($returnMessage);
            $helperLog->info("Current order status: " . $order->getStatusLabel());
            return $returnMessage;
        }

        $invoiceResult = $this->doFullInvoice($order, $orderReference, $helperLog);

        return $invoiceResult;
    }

    /**
     * @param $order
     * @param $helperLog
     * @param $messageLabel
     * @param $capturedAmountInCents
     * @param $status
     * @return string
     */
    public function underPaid($order, $helperLog, $messageLabel, $capturedAmountInCents, $status)
    {
        if ($order->canUnhold()) {
            $helperLog->info("{$messageLabel} | unholded.");
            $order->unhold();
        }

        $returnMessage = "OK | {$messageLabel} | Transaction status '{$status}'";
        $returnMessage .= "processed. Order status updated.";

        $amountInDecimal = $capturedAmountInCents * 0.01;

        $order->addStatusHistoryComment('MP - Captured offline amount of R$' . $amountInDecimal, false);
        $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 'underpaid');
        $order->setBaseTotalPaid($amountInDecimal);
        $order->setTotalPaid($amountInDecimal);
        $order->save();

        $helperLog->info($returnMessage);
        $helperLog->info("Current order status: " . $order->getStatusLabel());

        return $returnMessage;
    }

    /**
     * Create invoice
     * @todo must be deprecated use Uecommerce_Mundipagg_Model_Order_Payment createInvoice
     * @param Mage_Sales_Model_Order $order
     * @param array $data
     * @param float $totalPaid
     * @param string $status
     * @return string OK|KO
     */
    private function createInvoice($order, $data, $totalPaid, $status) {
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        $helperLog = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
        $returnMessageLabel = "Order #{$order->getIncrementId()}";
        if (!$invoice->getTotalQty()) {
            $returnMessage = 'Cannot create an invoice without products.';
            $order->addStatusHistoryComment("MP - " . $returnMessage, false);
            $order->save();
            $helperLog->info("{$returnMessageLabel} | {$returnMessage}");
            return $returnMessage;
        }
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(true);
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->setCanVoidFlag(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transactionSave->save();
        // Send invoice email if enabled
        if (Mage::helper('sales')->canSendNewInvoiceEmail($order->getStoreId())) {
            $invoice->sendEmail(true);
            $invoice->setEmailSent(true);
        }
        $order->setBaseTotalPaid($totalPaid);
        $order->setTotalPaid($totalPaid);
        $order->addStatusHistoryComment('MP - Captured offline', false);
        $payment = $order->getPayment();
        $payment->setAdditionalInformation('OrderStatusEnum', $data['OrderStatus']);
        if ($payment->getAdditionalInformation('PaymentMethod') == 'mundipagg_creditcard') {
            $payment->setAdditionalInformation('CreditCardTransactionStatusEnum', $data['CreditCardTransaction']['CreditCardTransactionStatus']);
        }
        if ($payment->getAdditionalInformation('PaymentMethod') == 'mundipagg_boleto') {
            $payment->setAdditionalInformation('BoletoTransactionStatusEnum', $data['BoletoTransaction']['BoletoTransactionStatus']);
        }
        if (isset($data['OnlineDebitTransaction']['BankPaymentDate'])) {
            $payment->setAdditionalInformation('BankPaymentDate', $data['OnlineDebitTransaction']['BankPaymentDate']);
        }
        if (isset($data['OnlineDebitTransaction']['BankName'])) {
            $payment->setAdditionalInformation('BankName', $data['OnlineDebitTransaction']['BankName']);
        }
        if (isset($data['OnlineDebitTransaction']['Signature'])) {
            $payment->setAdditionalInformation('Signature', $data['OnlineDebitTransaction']['Signature']);
        }
        if (isset($data['OnlineDebitTransaction']['TransactionIdentifier'])) {
            $payment->setAdditionalInformation('TransactionIdentifier', $data['OnlineDebitTransaction']['TransactionIdentifier']);
        }
        $payment->save();
        $newStatus = 'processing';
        if (strtolower($status) == 'overpaid') {
            $newStatus = 'overpaid';
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'overpaid');
        } else {
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'MP - Boleto pago', true);
        }
        $order->save();
        $returnMessage = "OK | {$returnMessageLabel} | invoice created and order state changed to {$newStatus}.";
        $helperLog->info($returnMessage);
        return $returnMessage;
    }

    /**
     * @param $order
     * @param $return
     */
    protected function persistTotalChanges($order, $return)
    {
        $grandTotal = floatval($order->getGrandTotal());
        $discountAmount = floatval($order->getDiscountAmount());

        $payment = $order->getPayment();
        $payment
            ->setAmountPaid($grandTotal)
            ->setBaseAmountPaid($grandTotal);
        $payment
            ->save();

        $order
            ->setTotalPaid($grandTotal)
            ->setBaseTotalPaid($grandTotal)
            ->setTotalInvoiced($grandTotal)
            ->setBaseTotalInvoiced($grandTotal);
        $order
            ->save();;

        $return
            ->setGrandTotal($grandTotal)
            ->setBaseGrandTotal($grandTotal)
            ->setDiscountAmount($discountAmount)
            ->setBaseDiscountAmount($discountAmount)
            ->save();
    }

    /**
     * @param $order
     * @param $returnMessageLabel
     * @param $capturedAmountInCents
     * @param $data
     * @param $status
     * @param $helperLog
     * @return string
     */
    protected function doPartialInvoice($order, $returnMessageLabel, $capturedAmountInCents, $data, $status, $helperLog)
    {
        $baseTotalPaid = $order->getTotalPaid();
        // If there is already a positive baseTotalPaid value it's not the first transaction
        if ($baseTotalPaid > 0) {
            $baseTotalPaid += $capturedAmountInCents * 0.01;
        } else {
            $baseTotalPaid = $capturedAmountInCents * 0.01;
        }
        $order->setTotalPaid($baseTotalPaid);
        $accOrderGrandTotal = sprintf(round($order->getGrandTotal(), 2));
        $accBaseTotalPaid = sprintf($baseTotalPaid);
        // Can invoice only if total captured amount is equal or greater to GrandTotal
        if ($accBaseTotalPaid < $accOrderGrandTotal) {
            $order->save();
            $returnMessage = "OK | {$returnMessageLabel} | ";
            $returnMessage .= "Captured amount isn't equal or greater to grand total, invoice not created.";
            $returnMessage .= "Transaction status '{$status}' received.";
            $helperLog->info($returnMessage);
            $helperLog->info("Current order status: " . $order->getStatusLabel());
            return $returnMessage;
        }

        if ($accBaseTotalPaid > $accOrderGrandTotal) {
            $order->setTotalPaid($accOrderGrandTotal);
        }
        return $this->createInvoice($order, $data, $accOrderGrandTotal, $status);
    }

    /**
     * @param $order
     * @param $orderReference
     * @param $helperLog
     * @return string
     */
    protected function doFullInvoice($order, $orderReference, $helperLog)
    {
        try {
            $orderPayment = new Uecommerce_Mundipagg_Model_Order_Payment();
            $return = $orderPayment->createInvoice($order);
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            $returnMessage = "OK | #{$orderReference} | ";
            $returnMessage .= "Can't pay order: {$errMsg}";
            $helperLog->info($returnMessage);
            $helperLog->info("Current order status: " . $order->getStatusLabel());
            return $returnMessage;
        }
        if ($return instanceof Mage_Sales_Model_Order_Invoice) {
            $this->persistTotalChanges($order, $return);
            Mage::helper('mundipagg')->sendNewInvoiceEmail($return, $order);

            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'MP - Boleto pago', true);
            $order->save();

            $returnMessage = "OK | #{$orderReference} | Invoice created - Order Paid";
            $helperLog->info($returnMessage);
            $helperLog->info("#{$orderReference} | Invoice Created - Current order status: " . $order->getStatusLabel());
            return $returnMessage;
        }
        // can't pay order
        $returnMessage = "KO | #{$orderReference} | Can't pay order: ";
        $returnMessage .= $return;
        $helperLog->info($returnMessage);
        $helperLog->info("#{$orderReference} | Current order status: " . $order->getStatusLabel());
        return $returnMessage;
    }
}
