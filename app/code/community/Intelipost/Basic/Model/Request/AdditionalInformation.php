<?php

class Intelipost_Basic_Model_Request_AdditionalInformation
{
 
public $extra_cost_absolute;
public $lead_time_business_days; 
public $free_shipping;
public function fetchAIRequest($shippingData)
{
    Mage::log($shippingData->getPrazoProdutos());
    $this->extra_cost_absolute = 0;    
    $this->lead_time_business_days = 1;
    $this->free_shipping = 0;

    return $this;
}


}

