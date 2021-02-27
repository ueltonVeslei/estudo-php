<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Template_Store{

	/**
	 * Store Object
	 * @var object
	 */
	private $_store;

	/**
	 * Set Store
	 * @param object|int $store
	 * @param string $storeCurrency
	 * @return void
	 */
	public function setStore($store, $storeCurrency = NULL){

		if( is_object($store) ){
			$this->_store = $store;
		}else{
			$this->_store = Mage::getModel('core/store')->load($store);
		}

		if( $storeCurrency ){
			$this->_store->setCurrentCurrencyCode($storeCurrency);
		}

	}

	/**
	 * Get Store
	 * @return object
	 */
	public function getStore(){
		return $this->_store;
	}

	/**
	 * Get Store Data
	 * @param string $item
	 * @param array $params
	 * @return mixed
	 */
	public function getData($item, $params = array()){

		$store = $this->getStore();
		$value = '';

		switch( $item ){
			case 'id':
				$value = $store->getStoreId();
			break;
			case 'code':
				$value = $store->getCode();
			break;
			case 'name':
				$value = $store->getName();
			break;
			case 'frontend_name':
			case 'frontendName':
				$value = $store->getFrontendName();
			break;
			case 'locale':
				$value = Mage::app()->getLocale()->getLocaleCode();
			break;
			case 'currency_code':
			case 'currencyCode':
				$value = $store->getCurrentCurrencyCode();
			break;
			case 'url':
				$value = $store->getUrl();
			break;
			case 'config':
				$value = Mage::getStoreConfig($params[0], $store->getStoreId());
			break;
		}

		return $value;
	}

}
