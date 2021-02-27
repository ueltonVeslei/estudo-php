<?php

class Av5_LogViewer_Model_Reports extends Av5_LogViewer_Model_Collection_Abstract {
	
	protected $_baseDir;
	
	public function __construct() {
		$this->_baseDir = Mage::getBaseDir('var') . DS . 'report';
		parent::__construct();
	}

}