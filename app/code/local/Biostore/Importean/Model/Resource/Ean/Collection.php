<?php
class Biostore_Importean_Model_Resource_Ean_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {  
    	
    	$this->_init('importean/ean');
    	
    }  
}