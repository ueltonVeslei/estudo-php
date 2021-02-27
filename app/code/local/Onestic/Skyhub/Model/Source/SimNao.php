<?php
class Onestic_Skyhub_Model_Source_SimNao {
    
    public function toOptionArray()
    {
        return array(
            array('value'=> 'SIM', 'label'=>'SIM'),
            array('value'=> 'NÃO', 'label'=>'NÃO'),
        );
    }
    
    public function toColumnOptionArray()
    {
    	return array(
    	    'SIM'      =>  'SIM',
    	    'NÃO'      =>  'NÃO',
    	);
    } 
    
}

