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
 * Class Onestic_PaymentFee_Model_Sales_Quote_Address_Total_Fee
 */
class Onestic_PaymentFee_Model_Sales_Quote_Address_Total_Fee extends Mage_Sales_Model_Quote_Address_Total_Abstract {
    /**
     * @var string
     */
    protected $_code = 'fee';

    /**
     * Coletar taxa do total (address amount)
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Onestic_PaymentFee_Model_Sales_Quote_Address_Total_Fee
     */
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        parent::collect($address);
        $this->_setAmount(0);
        $this->_setBaseAmount(0);
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $address->getQuote();
        /* @var $feeModel Onestic_PaymentFee_Model_Fee */
        $feeModel = Mage::getModel('payment_fee/fee');
        if ($feeModel->canApply($address)) {
            $exist_amount = $quote->getFeeAmount();
            $fee          = $feeModel->getFee($address);
            $balance      = $fee - $exist_amount;
            $address->setFeeAmount($balance);
            $address->setBaseFeeAmount($balance);
            $quote->setFeeAmount($balance);
            $address->setGrandTotal($address->getGrandTotal() + $address->getFeeAmount());
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseFeeAmount());
        }

        return $this;
    }

    /**
     * Adicionar taxa ao endereço
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Onestic_PaymentFee_Model_Sales_Quote_Address_Total_Fee
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $amount = Mage::helper('payment_fee')->getFee();
        $paymentMethod = $address->getQuote()->getPayment();

        if ($amount != 0 && $address->getAddressType() == 'shipping' && is_object($paymentMethod)) {    // cobraça e envio
            $title = Mage::getModel('payment_fee/fee')->getTotalTitle(null, $address->getQuote());

            try {
                $methodCode = $paymentMethod->getMethodInstance()->getCode();
            } catch(\Exception $e) {
                return $this;
            }
            if (!isset($amount[$methodCode])) {
                return $this;
            }

            $address->addTotal(
                array(
                    'code' => $this->getCode(),
                    'title' => $amount[$methodCode]['description'],
                    'value' => $amount[$methodCode]['fee']
                )
            );
            return $this;
        }
    }
}
