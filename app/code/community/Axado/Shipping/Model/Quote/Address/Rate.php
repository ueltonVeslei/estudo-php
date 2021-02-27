<?php

class Axado_Shipping_Model_Quote_Address_Rate
    extends Mage_Sales_Model_Quote_Address_Rate
{
    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate)
    {   
        parent::importShippingRate($rate);

        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
            $this->setAxadoToken($rate->getData('token'));   
        }

        return $this;
    }
}
