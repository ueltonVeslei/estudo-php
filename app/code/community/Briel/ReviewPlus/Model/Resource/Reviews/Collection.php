<?php

class Briel_ReviewPlus_Model_Resource_Reviews_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('reviewplus/reviews');
    }
}
?>