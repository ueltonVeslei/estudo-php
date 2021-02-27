<?php

class Intelipost_Quote_Model_Sales_Quote_Address_Rate
extends Mage_Sales_Model_Quote_Address_Rate
// extends Mage_Shipping_Model_Rate_Abstract
{

public function importShippingRate (Mage_Shipping_Model_Rate_Result_Abstract $rate)
{
    $result = parent::importShippingRate ($rate);
   // Mage::log($rate->getCost ());

    if ($rate->getIntelipostRestrictedMsg ())
    {
    	$this->setIntelipostRestrictedMsg($rate->getIntelipostRestrictedMsg());
    }
    $this->setIntelipostQuoteId ($rate->getIntelipostQuoteId ());
    $this->setIntelipostCost ($rate->getIntelipostCost());
    $this->setIntelipostEstimatedDeliveryBusinessDays($rate->getIntelipostEstimatedDeliveryBusinessDays());

    return $result;
}

}

