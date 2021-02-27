<?php

class RMO_Integrator_Model_System_Config_Source_Storeview
{
    public static function toOptionArray()
    {
        $options = array(array('value' => '', 'label' => ''));
        
        foreach(Mage::app()->getStores() as $mageStore) {
            $options[] = array('value' => $mageStore->getCode(), 'label' => $mageStore->getName());
        }
        
        return $options;
    }
}