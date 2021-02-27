<?php

class Axado_Shipping_Model_Source_WeightType
{
    const WEIGHT_GR = 'gr';
    const WEIGHT_KG = 'kg';

    public function toOptionArray()
    {
        return array(
            array('value' => self::WEIGHT_GR, 'label' => Mage::helper('adminhtml')->__('Gramas')),
            array('value' => self::WEIGHT_KG, 'label' => Mage::helper('adminhtml')->__('Kilos')),
        );
    }
}
