<?php

class Intelipost_Quote_Model_Resource_Order_Price
{

public function toOptionArray()
{
    return array(
        array(
            'value' => 'asc',
            'label' => Mage::helper ('adminhtml')->__('Order by Price Asc'),
        ),
        array(
            'value' => 'desc',
            'label' => Mage::helper ('adminhtml')->__('Order by Price Desc'),
        ),
    );
}

}

