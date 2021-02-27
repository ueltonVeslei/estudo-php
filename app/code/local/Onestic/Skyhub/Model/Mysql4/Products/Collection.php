<?php 
class Onestic_Skyhub_Model_Mysql4_Products_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('onestic_skyhub/products');
	}
}