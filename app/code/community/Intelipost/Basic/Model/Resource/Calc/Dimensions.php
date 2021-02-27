<?php

class Intelipost_Basic_Model_Resource_Calc_Dimensions
{

public function toOptionArray()
{
    return array(
        array(
            'value' => 'no',
            'label' => Mage::helper('basic')->__('No'),
        ),
        array(
            'value' => 'weigth',
            'label' => Mage::helper('basic')->__('Weigth'),
        ),
        array(
            'value' => 'dimensions',
            'label' => Mage::helper('basic')->__('Dimensions'),
        ),
        array(
            'value' => 'unitary',
            'label' => Mage::helper('basic')->__('Unitary'),
        )
    );
}

}

