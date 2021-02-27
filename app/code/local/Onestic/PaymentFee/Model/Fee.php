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
 * Class Onestic_PaymentFee_Model_Fee
 */
class Onestic_PaymentFee_Model_Fee extends Mage_Core_Model_Abstract {
    /**
     * Total
     */
    const TOTAL_CODE = 'fee';
    /**
     * @var array
     */
    public $methodFee = NULL;

    /**
     * Construtor
     */
    public function __construct() {
        $this->_getMethodFee();
    }

    /**
     * Recuperar taxas dos meios de pagamento das configurações da loja
     * @return array
     */
    protected function _getMethodFee() {
        if (is_null($this->methodFee)) {
            $this->methodFee = Mage::helper('payment_fee')->getFee();
        }

        return $this->methodFee;
    }

    /**
     * Checar se a taxa pode ser aplicada
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return bool
     */
    public function canApply(Mage_Sales_Model_Quote_Address $address) {
        /* @var $helper Onestic_PaymentFee_Helper_Data */
        $helper = Mage::helper('payment_fee');
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $address->getQuote();
        if ($helper->isEnabled()) {
            if ($method = $quote->getPayment()->getMethod()) {
                if (isset($this->methodFee[$method])) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     * Calcular a taxa que deve ser aplicada
     * @param Mage_Sales_Model_Quote_Address $address
     * @return float|int
     */
    public function getFee(Mage_Sales_Model_Quote_Address $address) {
        /* @var $helper Onestic_PaymentFee_Helper_Data */
        $helper = Mage::helper('payment_fee');
        /* @var $quote Mage_Sales_Model_Quote */
        $quote   = $address->getQuote();
        $method  = $quote->getPayment()->getMethod();
        $fee     = $this->methodFee[$method]['fee'];
        $feeType = $helper->getFeeType();
        if ($feeType == Mage_Shipping_Model_Carrier_Abstract::HANDLING_TYPE_FIXED) {
            return $fee;
        } else {
            $totals = $quote->getTotals();
            $sum    = 0;
            foreach ($totals as $total) {
                if ($total->getCode() != self::TOTAL_CODE) {
                    $sum += (float)$total->getValue();
                }
            }

            return ($sum * ($fee / 100));
        }
    }

    /**
     * Recuperar o título da taxa
     * @param string $method
     * @param Mage_Sales_Model_Quote $quote
     * @return string
     */
    public function getTotalTitle($method = '', Mage_Sales_Model_Quote $quote = null) {
        $title = '';
        if (!$method) {
            $method = $quote->getPayment()->getMethod();
        }
        if ($method) {
            if (isset($this->methodFee[$method]) && $this->methodFee[$method]['description']) {
                $title = $this->methodFee[$method]['description'];
            }
        }
        if (!$title) {
            /* @var $helper Onestic_PaymentFee_Helper_Data */
            $helper = Mage::helper('payment_fee');
            $title  = $helper->__($helper->getConfig('default_description'));
        }

        return $title;
    }
}