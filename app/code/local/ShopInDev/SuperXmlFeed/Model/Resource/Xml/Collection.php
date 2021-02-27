<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Resource_Xml_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	/**
	 * Init collection
	 * @return void
	 */
	public function _construct(){
		$this->_init('superxmlfeed/xml');
	}

	/**
	 * Filter collection by specified store ids
	 * @param array|int $storeIds
	 * @return ShopInDev_SuperXmlFeed_Model_Resource_Xml_Collection
	 */
	public function addStoreFilter($storeIds){
		$this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
		return $this;
	}

}
