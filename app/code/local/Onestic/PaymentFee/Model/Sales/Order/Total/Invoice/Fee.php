<?php
/**
 * Criado por Onestic
 * Baseado no módulo "Magentix" (https://github.com/magentix/Fee)
 *
 * @category   Onestic
 * @package    Onestic_PaymentFee
 * @author     Felipe Macedo (f.macedo@onestic.com)
 * @license    Módulo gratuito, pode ser redistribuido e/ou modificado
 */
/**
 * Class Onestic_PaymentFee_Model_Sales_Order_Total_Invoice_Fee
 */
class Onestic_PaymentFee_Model_Sales_Order_Total_Invoice_Fee extends Mage_Sales_Model_Order_Invoice_Total_Abstract {
    /**
     * Coletar Invoice Total
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Onestic_PaymentFee_Model_Sales_Order_Total_Invoice_Fee
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $order = $invoice->getOrder();
        $feeAmountLeft     = $order->getFeeAmount() - $order->getFeeAmountInvoiced();
        $baseFeeAmountLeft = $order->getBaseFeeAmount() - $order->getBaseFeeAmountInvoiced();
        if (abs($baseFeeAmountLeft) < $invoice->getBaseGrandTotal()) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $feeAmountLeft);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseFeeAmountLeft);
        } else {
            $feeAmountLeft     = $invoice->getGrandTotal() * -1;
            $baseFeeAmountLeft = $invoice->getBaseGrandTotal() * -1;
            $invoice->setGrandTotal(0);
            $invoice->setBaseGrandTotal(0);
        }
        $invoice->setFeeAmount($feeAmountLeft);
        $invoice->setBaseFeeAmount($baseFeeAmountLeft);

        return $this;
    }
}
