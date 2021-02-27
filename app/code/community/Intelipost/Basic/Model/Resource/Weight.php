<?php

class Intelipost_Basic_Model_Resource_Weight
{

public function toOptionArray()
{
    return array(
        array(
            'value' => 'gr',
            'label' => Mage::helper('adminhtml')->__('Grams'),
        ),
        array(
            'value' => 'kg',
            'label' => Mage::helper('adminhtml')->__('KiloGrams'),
        ),
    );
}

}

