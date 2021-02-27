<?php

class Intelipost_Basic_Model_Resource_Volume
{

public function toOptionArray()
{
    return array(
        array(
            'value' => 'cm',
            'label' => Mage::helper('adminhtml')->__('Centimeters'),
        ),
        array(
            'value' => 'mt',
            'label' => Mage::helper('adminhtml')->__('Meters'),
        ),
    );
}

}

