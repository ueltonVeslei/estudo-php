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
 * Class Onestic_PaymentFee_Block_Sales_Order_Fee
 */
class Onestic_PaymentFee_Block_Sales_Order_Fee extends Mage_Core_Block_Template {
    /**
     * Inicializar os totais das taxas
     *
     * @return Onestic_PaymentFee_Block_Sales_Order_Fee
     */
    public function initTotals() {
        if ((float)$this->getOrder()->getBaseFeeAmount()) {
            $source = $this->getSource();
            $value  = $source->getFeeAmount();
            $method = $this->getOrder()->getPayment()->getMethod();
            $title  = Mage::getModel('payment_fee/fee')->getTotalTitle($method);
            $this->getParentBlock()->addTotal(new Varien_Object(array(
                                                                     'code'   => 'fee',
                                                                     'strong' => FALSE,
                                                                     'label'  => $title,
                                                                     'value'  => $value
                                                                )));
        }

        return $this;
    }

    /**
     * Obter o objeto do Pedido
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder() {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Obter total do objeto
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource() {
        return $this->getParentBlock()->getSource();
    }
}