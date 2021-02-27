<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Resource_Xml extends Mage_Core_Model_Resource_Db_Abstract {

	/**
	 * Init resource model
	 * @return void
	 */
	protected function _construct(){
		$this->_init('superxmlfeed/xml', 'xml_id');
	}

}
