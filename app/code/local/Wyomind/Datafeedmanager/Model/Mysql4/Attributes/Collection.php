<?php

class Wyomind_Datafeedmanager_Model_Mysql4_Attributes_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('datafeedmanager/attributes');
	}
}