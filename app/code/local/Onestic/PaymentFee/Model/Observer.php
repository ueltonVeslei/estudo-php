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
 * Class Onestic_PaymentFee_Model_Observer
 */
class Onestic_PaymentFee_Model_Observer extends Mage_Core_Model_Abstract {
    /**
     * Setar taxa na fatura do pedido
     *
     * @param Varien_Event_Observer $observer
     * @return Onestic_PaymentFee_Model_Observer
     */
    public function invoiceSaveAfter(Varien_Event_Observer $observer) {
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->getBaseFeeAmount()) {
            $order = $invoice->getOrder();
            $order->setFeeAmountInvoiced($order->getFeeAmountInvoiced() + $invoice->getFeeAmount());
            $order->setBaseFeeAmountInvoiced($order->getBaseFeeAmountInvoiced() + $invoice->getBaseFeeAmount());
        }

        return $this;
    }

    /**
     * Setar taxa no estorno do pedido
     *
     * @param Varien_Event_Observer $observer
     * @return Onestic_PaymentFee_Model_Observer
     */
    public function creditmemoSaveAfter(Varien_Event_Observer $observer) {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($creditmemo->getFeeAmount()) {
            $order = $creditmemo->getOrder();
            $order->setFeeAmountRefunded($order->getFeeAmountRefunded() + $creditmemo->getFeeAmount());
            $order->setBaseFeeAmountRefunded($order->getBaseFeeAmountRefunded() + $creditmemo->getBaseFeeAmount());
        }

        return $this;
    }

    /**
     * Atualizar total do PayPal
     *
     * @param Varien_Event_Observer $observer
     * @return Onestic_PaymentFee_Model_Observer
     */
    public function updatePaypalTotal(Varien_Event_Observer $observer) {
        $cart = $observer->getEvent()->getPaypalCart();
        $cart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_SUBTOTAL, $cart->getSalesEntity()->getFeeAmount());

        return $this;
    }
}
