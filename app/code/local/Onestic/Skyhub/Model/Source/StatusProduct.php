<?php
class Onestic_Skyhub_Model_Source_StatusProduct {
    
    public function toOptionArray()
    {
        return array(
            array('value'=> 'enabled', 'label'=>'Habilitado'),
        	array('value'=> 'disabled', 'label'=>'Desativado'),
        );
    }
    
    public function toColumnOptionArray()
    {
    	return array(
    	    'enabled' => 'Habilitado',
        	'disabled' => 'Desativado',
    	);
    } 
    
}

