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
 * Class Onestic_PaymentFee_Model_Sales_Order_Total_Creditmemo_Fee
 */
class Onestic_PaymentFee_Model_Sales_Order_Total_Creditmemo_Fee extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract 
{
    /**
     * Coletar Credit Memo Total
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return Onestic_PaymentFee_Model_Sales_Order_Total_Creditmemo_Fee
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        $order = $creditmemo->getOrder();
        if ($order->getFeeAmountInvoiced() > 0) {
            $feeAmountLeft     = max($order->getFeeAmount(), $order->getFeeAmountInvoiced() - $order->getFeeAmountRefunded());
            $basefeeAmountLeft = max($order->getBaseFeeAmount(), $order->getBaseFeeAmountInvoiced() - $order->getBaseFeeAmountRefunded());
            if ($basefeeAmountLeft > 0) {
                $capBaseAmount = min($order->getBaseTotalPaid(), $creditmemo->getBaseGrandTotal() + $basefeeAmountLeft);
                $capAmount = min($order->getTotalPaid(), $creditmemo->getGrandTotal() + $feeAmountLeft);

                $creditmemo->setGrandTotal($capAmount);
                $creditmemo->setBaseGrandTotal($capBaseAmount);
                $creditmemo->setFeeAmount($feeAmountLeft);
                $creditmemo->setBaseFeeAmount($basefeeAmountLeft);
            }
        } else {
            $feeAmount     = $order->getFeeAmountInvoiced();
            $basefeeAmount = $order->getBaseFeeAmountInvoiced();
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmount);
            $creditmemo->setFeeAmount($feeAmount);
            $creditmemo->setBaseFeeAmount($basefeeAmount);
        }

        return $this;
    }
}
