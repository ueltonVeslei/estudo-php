<?php
class Db {

	protected $_conn 		= NULL;

	public function __construct() {
		if(!$this->_conn) {
			// Create connection
			$this->_conn = Mage::getSingleton('core/resource')->getConnection('core_write');
		}

		return $this->_conn;
	}

}