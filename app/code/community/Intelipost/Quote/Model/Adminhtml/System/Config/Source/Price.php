<?php

class Intelipost_Quote_Model_Adminhtml_System_Config_Source_Price
{

public function toOptionArray()
{
    return array(
        array ('value' => 'product', 'label' => Mage::helper ('basic')->__('Product')),
        array ('value' => 'cart',    'label' => Mage::helper ('basic')->__('Cart')),
    );
}

}

