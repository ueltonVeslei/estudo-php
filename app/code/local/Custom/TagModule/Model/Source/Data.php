<?php 
    class Custom_TagModule_Model_Source_Data extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
    {
        public function getAllOptions()
        {
            $options = array(
                1 => 'Não',
                2 => 'Sim',                      
            );
            return $options;
        }

    }
