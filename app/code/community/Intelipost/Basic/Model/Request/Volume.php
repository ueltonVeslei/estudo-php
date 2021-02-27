<?php

class Intelipost_Basic_Model_Request_Volume
{

public $weight;
public $volume_type;
public $cost_of_goods;
public $width;
public $height;
public $length;   

public function fetchVolumeRequest($shippingData, $boxAdditionalWeigth)
{
    $this->weight = $shippingData->getDimension()->getWeigth() + $boxAdditionalWeigth;
    $this->volume_type = 'BOX';
    $this->cost_of_goods = $shippingData->getPackagePrice();
    $this->width = $shippingData->getDimension()->getWidth();
    $this->height = $shippingData->getDimension()->getHeight();
    $this->length = $shippingData->getDimension()->getLength();

    return $this;
}

public function fetchMultiVolumeRequest($packageArray, $boxAdditionalWeigth)
{

    $this->weight = $packageArray['weight'] + $boxAdditionalWeigth;
    $this->volume_type = 'BOX';
    $this->cost_of_goods = $packageArray['price'];
    $this->width = $packageArray['width'];
    $this->height = $packageArray['height'];
    $this->length = $packageArray['length'];

    return $this;
}

}

