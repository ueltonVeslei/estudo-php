<?php

class Intelipost_Quote_Model_Request_Quote
// extends Varien_Object
{

public $origin_zip_code;
public $destination_zip_code;
public $volumes = array();
public $additional_information = array();

public function fetchQuoteRequest($shippingData)
{            
    $this->origin_zip_code = $shippingData->getOriginZipCode();
    $this->destination_zip_code = $shippingData->getDestZipCode();        

    $this->fetchVolume($shippingData);

    if ($shippingData->hasPrazoProdutos()) {
        $this->fetchAdditionalInformation($shippingData);
    }

    return $this;

}

private function fetchVolume($shippingData)
{
    if (Mage::getStoreConfig('intelipost_basic/settings/advanced_vol_calc') != 'no')
    {            
        $package = $shippingData->getDimension()->getPackages();
        
        for ($i = 0; $i < count($package); $i++)
        {
            $volume = $this->_getBasicVolume ();
            $volume->fetchMultiVolumeRequest($package[$i]);

            array_push($this->volumes, $volume);
        }
        
    }
    else
    {
        $volume = $this->_getBasicVolume ();
        $volume->fetchVolumeRequest($shippingData);

        array_push($this->volumes, $volume);
    }

    return $this;
}

private function fetchAdditionalInformation($shippingData)
{
    $this->additional_information = array("lead_time_business_days" => $shippingData->getPrazoProdutos());

    return $this;
}
private function _getBasicVolume ()
{
    return Mage::getModel ('basic/request_volume');
}

}

