<?php

class Briel_ReviewPlus_Model_Reviews extends Mage_Core_Model_Abstract {
	
	protected function _construct() {
		parent::_construct();
		$this->_init('reviewplus/reviews');
	}
}
?>