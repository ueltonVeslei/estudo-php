<?php

class Briel_ReviewPlus_Model_Resource_Clientlog_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
    public function _construct() {
        parent::_construct();
        $this->_init('reviewplus/clientlog');
    }
}
?>