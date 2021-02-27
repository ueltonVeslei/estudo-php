<?php

class Briel_ReviewPlus_Model_Resource_Reviews extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('reviewplus/reviews', 'id');
	}
}
?>