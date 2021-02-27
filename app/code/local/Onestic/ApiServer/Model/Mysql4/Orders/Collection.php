<?php 
class Onestic_ApiServer_Model_Mysql4_Orders_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('onestic_apiserver/orders');
	}
}