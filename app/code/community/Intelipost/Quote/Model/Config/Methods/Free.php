<?php

class Intelipost_Quote_Model_Config_Methods_Free
{        

public function toOptionArray()
{
	$result = array(
        array(
            'value' => '',
            'label' => Mage::helper ('quote')->__('')
        ),
        array(
            'value' => 'lower-price',
            'label' => Mage::helper ('quote')->__('Lower Price'),
        ),
        array(
            'value' => 'lower-cost',
            'label' => Mage::helper ('quote')->__('Lower Cost'),
        ),
        array(
            'value' => 'lower_delivery_date',
            'label' => Mage::helper ('quote')->__('Lower Delivery Date')
        ),
        array(
            'value' => 'greater_delivery_date',
            'label' => Mage::helper ('quote')->__('Greater Delivery Date')
        ),
	);

    $methods = Mage::getModel ('quote/carrier_intelipost')->getAllowedMethods ();
    foreach ($methods as $id => $value)
    {
        $result [] = array(
            'value' => $id,
            'label' => $value,
        );
    }

    return $result;
}

}

