<?php
class Onestic_ApiServer_Model_Source_Sync {
    
    public function toOptionArray()
    {
        return array(
            array('value'=> 'SYNCED', 'label'=>'SIM'),
            array('value'=> 'NOT_SYNCED', 'label'=>'NÃO'),
        );
    }
    
    public function toColumnOptionArray()
    {
    	return array(
    	    'SYNCED'           =>  'SIM',
    	    'NOT_SYNCED'       =>  'NÃO',
    	);
    } 
    
}

