<?php
class Biostore_Importean_Model_Resource_Ean
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {  
    	$this->_init('importean/ean', 'ean_id');
    }  
}