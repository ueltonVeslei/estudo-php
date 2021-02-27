<?php

class RMO_Integrator_Model_System_Config_Source_Visibility
{
    public static function toOptionArray()
    {
        $res = array();
        foreach (Mage::getModel("catalog/product_visibility")->getOptionArray() as $index => $value) {
            $res[] = array(
               'value' => $index,
               'label' => $value
            );
        }
        return $res;
    }
}