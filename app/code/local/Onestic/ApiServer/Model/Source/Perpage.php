<?php
class Onestic_ApiServer_Model_Source_Perpage {
    
    public function toOptionArray()
    {
        return array(
            array('value'=> 5, 'label'=>'5'),
            array('value'=> 10, 'label'=>'10'),
        	array('value'=> 20, 'label'=>'20'),
            array('value'=> 50, 'label'=>'50'),
            array('value'=> 100, 'label'=>'100'),
        );
    }
    
    public function toColumnOptionArray()
    {
    	return array(
    	    5      =>  '5',
    	    10     =>  '10',
    	    20     =>  '20',
    	    50     =>  '50',
    	    100    =>  '100',
    	);
    } 
    
}

