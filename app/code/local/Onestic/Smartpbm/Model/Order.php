<?php
class Onestic_Smartpbm_Model_Order extends Mage_Core_Model_Abstract {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('smartpbm/order');
	}

	public function getItemsByPbm($pbm, $orderId) {
	    $collection = $this->getCollection()
            ->addFieldToFilter('pbm', $pbm)
            ->addFieldToFilter('order_id', $orderId);

	    $items = [];
	    foreach ($collection as $item) {
	        $items[] = $item;
        }

	    return $items;
    }
}