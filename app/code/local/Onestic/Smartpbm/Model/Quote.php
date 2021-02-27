<?php
class Onestic_Smartpbm_Model_Quote extends Mage_Core_Model_Abstract {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('smartpbm/quote');
	}

	public function getItemsByPbm($pbm, $quoteId) {
	    $collection = $this->getCollection()
            ->addFieldToFilter('pbm', $pbm)
            ->addFieldToFilter('quote_id', $quoteId);

	    $items = [];
	    foreach ($collection as $item) {
	        $items[] = $item;
        }

	    return $items;
    }
}