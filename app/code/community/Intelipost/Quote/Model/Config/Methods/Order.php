<?php

class Intelipost_Quote_Model_Config_Methods_Order
{        

public function toOptionArray()
{
	return array(
		array(
            'value' => 'lower-price',
            'label' => Mage::helper ('quote')->__('Lower Price')
        ),
	    array(
            'value' => 'lower_delivery_date',
            'label' => Mage::helper ('quote')->__('Lower Delivery Date')
        ),
	);
}

}

