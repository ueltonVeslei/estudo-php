<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Template_Xml{

	/**
	 * XML Object
	 * @var object
	 */
	private $_xml;

	/**
	 * XML Items
	 * @var string
	 */
	private $_xmlItems;

	/**
	 * Set XML
	 * @param object $xml
	 * @return void
	 */
	public function setXml($xml){
		$this->_xml = $xml;
	}

	/**
	 * Get XML
	 * @return string
	 */
	public function getXml(){
		return $this->_xml;
	}

	/**
	 * Set XML Items
	 * @param string $item
	 * @return void
	 */
	public function setXmlItems($items){
		$this->_xmlItems = $items;
	}

	/**
	 * Get XML Items
	 * @return string
	 */
	public function getXmlItems(){
		return $this->_xmlItems;
	}

	/**
	 * Get XML Data
	 * @param string $item
	 * @param array $params
	 * @return mixed
	 */
	public function getData($item, $params = array()){

		$value = '';
		$xml = $this->getXml();

		switch( $item ){
			case 'id':
				$value = $xml->getXmlId();
			break;
			case 'filename':
				$value = $xml->getXmlFilename();
			break;
			case 'path':
				$value = $xml->getXmlPath();
			break;
			case 'store_id':
			case 'storeId':
				$value = $xml->getStoreId();
			break;
			case 'updated':
				$format = (isset($params[0]) AND $params[0]) ? $params[0] : 'Y-m-d H:i:s';
				$value = Mage::getSingleton('core/date')->gmtDate($format);
			break;
			case 'products':
			case 'items':
				$value = $this->getXmlItems();
			break;
		}

		return $value;
	}

}
