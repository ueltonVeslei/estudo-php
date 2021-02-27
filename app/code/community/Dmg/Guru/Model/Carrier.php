<?php

class Dmg_Guru_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'guru_carrier';
    protected $_isFixed = true;

    public function getAllowedMethods()
    {
        return array('standard' => 'Standard');
    }

    public function isTrackingAvailable()
    {
        return true;
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::app()->getStore()->isAdmin()) {
            return;
        }

        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');
        $result->append($this->_getStandardRate());

        return $result;
    }

    protected function _getStandardRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle('Guru Carrier');
        $rate->setMethod('standard');
        $rate->setMethodTitle('Standard');
        $rate->setPrice(0);
        $rate->setCost(0);

        return $rate;
    }
}
