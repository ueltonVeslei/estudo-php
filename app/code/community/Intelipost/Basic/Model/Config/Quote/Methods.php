<?php

class Intelipost_Basic_Model_Config_Quote_Methods
{

public function toOptionArray()
{
    return array(
        array(
            'value' => 'product',
            'label' => Mage::helper('adminhtml')->__('Product'),
        ),
        array(
            'value' => 'dimensions',
            'label' => Mage::helper('adminhtml')->__('Dimensions'),
        ),
    );
}

}

