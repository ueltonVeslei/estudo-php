<?php

class Intelipost_Quote_Model_Resource_RoundPrices
{

public function toOptionArray()
{
    return array(
        array(
            'value' => '0',
            'label' => Mage::helper ('quote')->__('No'),
        ),
        array(
            'value' => 'up',
            'label' => Mage::helper ('quote')->__('Round Up'),
        ),
        array(
            'value' => 'down',
            'label' => Mage::helper ('quote')->__('Round Down'),
        ),
    );
}

}

