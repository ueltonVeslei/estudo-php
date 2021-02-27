<?php 
class Onestic_Smartpbm_Model_Resource_Products_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('smartpbm/products');
	}
}