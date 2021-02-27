<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Categories object
	 * @access private
	 * @var array
	 */
	private $_categories = array();

	/**
	 * Retrieve and store category
	 * @param int $id
	 * @param int $storeId
	 * @return object
	 */
	public function loadCategory($id, $storeId){

		if( !isset($this->_categories[ $id ]) ){

			$category = Mage::getModel('catalog/category')
					->setStoreId( $storeId )
					->load( $id );

			$this->_categories[ $id ] = $category;

		}

		return $this->_categories[ $id ];
	}

}
