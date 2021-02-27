<?php

class Intelipost_Quote_Model_Config_Attributes_Subsidio_Frete
{
    public function toOptionArray()
    {        
        $options =  array(
        				array( 'value' => -1, 
        					'label' => Mage::helper ('quote')->__('Select')        					
        					 ),
        				array( 'value' => 0,
        					   'label' => Mage::helper('quote')->__('Disabled')
        					 ),
        				array( 'value' => 1
        						'label' => Mage::helper('quote')->__('Enabled')
        					),	
        			);

        return $options;
        //return $this->_options
    }
}