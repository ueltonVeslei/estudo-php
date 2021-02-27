<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Template{

	/**
	 * XML Parses
	 * @var array
	 */
	private $_parses = array();

	/**
	 * Template XML Object
	 * @var object
	 */
	private $_templateXml;

	/**
	 * Template Store Object
	 * @var object
	 */
	private $_templateStore;

	/**
	 * Template Product Object
	 * @var object
	 */
	private $_templateProduct;

	/**
	 * Set XML Parses
	 * @param array $parses
	 * @return void
	 */
	public function setParses($parses){
		$this->_parses = $parses;
	}

	/**
	 * Get the XML Parses
	 * @return array
	 */
	public function getParses(){
		return $this->_parses;
	}

	/**
	 * Set XML Template
	 * @param object $xml
	 * @return void
	 */
	public function setTemplateXml($xml){

		if( !$this->_templateXml ){
			$this->_templateXml = Mage::getSingleton('superxmlfeed/template_xml');
		}

		$this->_templateXml->setXml($xml);
	}

	/**
	 * Retrieve XML Template
	 * @return object
	 */
	public function getTemplateXml(){
		return $this->_templateXml;
	}

	/**
	 * Set Store Template
	 * @param object $store
	 * @param string $storeCurrency
	 * @return void
	 */
	public function setTemplateStore($store, $storeCurrency = NULL){

		if( !$this->_templateStore ){
			$this->_templateStore = Mage::getSingleton('superxmlfeed/template_store');
		}

		$this->_templateStore->setStore($store, $storeCurrency);
	}

	/**
	 * Retrieve XML Template
	 * @return object
	 */
	public function getTemplateStore(){
		return $this->_templateStore;
	}

	/**
	 * Set Product Template
	 * @param object $product
	 * @return void
	 */
	public function setTemplateProduct($product){

		if( !$this->_templateProduct ){
			$storeId = $this->getTemplateStore()->getStore()->getStoreId();
			$this->_templateProduct = Mage::getSingleton('superxmlfeed/template_product');
			$this->_templateProduct->setStoreId( $storeId );
		}

		$this->_templateProduct->setProduct($product);
		$this->_templateProduct->resetChildProducts();

	}

	/**
	 * Retrieve Product Template
	 * @return object
	 */
	public function getTemplateProduct(){
		return $this->_templateProduct;
	}

	/**
	 * Create XML wrapper object for XML feed
	 * @return string
	 */
	public function createXmlWrapper(){

		$XMLWrapper = $this->getTemplateXml()->getXml()->getXmlWrapper();

		$this->setParses(array(
			'STORE' => 'getStoreData',
			'XML' => 'getXmlData',
		));

		return $this->parseData($XMLWrapper);
	}

	/**
	 * Create XML item object for XML feed
	 * @param object $product
	 * @return string
	 */
	public function createXmlItem(){

		$XMLItem = $this->getTemplateXml()->getXml()->getXmlItem();

		$this->setParses(array(
			'PRODUCT::STOCK' => 'getProductStockData',
			'PRODUCT::CATEGORY' => 'getProductCategoriesData',
			'PRODUCT::SUBCATEGORY' => 'getProductCategoriesData',
			'PRODUCT::CATEGORIES' => 'getProductCategoriesData',
			'PRODUCT::ATTRIBUTE' => 'getProductAttributeData',
			'PRODUCT::CHILDS' => 'getProductChildsData',
			'PRODUCT' => 'getProductData',
			'CHILD::STOCK' => 'getProductStockData',
			'CHILD::CATEGORY' => 'getProductCategoriesData',
			'CHILD::SUBCATEGORY' => 'getProductCategoriesData',
			'CHILD::CATEGORIES' => 'getProductCategoriesData',
			'CHILD::ATTRIBUTE' => 'getProductAttributeData',
			'CHILD' => 'getProductData',
			'STORE' => 'getStoreData',
			'XML' => 'getXmlData',
		));

		// TODO: Shipping, Payment Options

		return $this->parseData($XMLItem);
	}

	/**
	 * Dispatch event for retrieving external data
	 * Events:
	 *   super_xml_feed_xml_item_get_value
	 *   super_xml_feed_store_item_get_value
	 *   super_xml_feed_product_item_get_value
	 *   super_xml_feed_product_childs_item_get_value
	 *   super_xml_feed_product_stock_item_get_value
	 *   super_xml_feed_product_categories_item_get_value
	 *   super_xml_feed_product_attribute_item_get_value
	 *
	 * @param string $type
	 * @param array $data
	 * @return mixed
	 */
	public function dispatchEvent($type, $data = array()){

		$value = new Varien_Object();
		$value->setValue(NULL);

		$data['value'] = $value;

		Mage::dispatchEvent('super_xml_feed_'. $type. '_item_get_value', $data);

		return $value->getValue();
	}

	/**
	 * Parse data to replace values
	 * @param string $pattern
	 * @param string $string
	 * @param boolean $ifResult
	 * @return string
	 */
	public function parseReplaceData($pattern, $string, $ifResult = TRUE){

		if( preg_match_all($pattern, $string, $matches, PREG_SET_ORDER) ){

			foreach( $matches as $match ){

				$condition = $this->getData($match[1]);
				$condition = trim($condition);
				$replacedValue = '';

				if( $ifResult && $condition
				 	OR !$ifResult && !$condition ){
					$replacedValue = $match[2];
				}
				
				$string = str_replace($match[0], $replacedValue, $string);

			}

		}

		return $string;
	}

	/**
	 * Parse data to replace values
	 * @param string $string
	 * @return string
	 */
	public function parseData($string){

		// Loops - FOREACH - CHILD
		$foreachPattern = '/{{?FOREACH PRODUCT::CHILDS as CHILD}?}(.*?){{?\\/FOREACH\s*}?}/is';

		if( preg_match_all($foreachPattern, $string, $matches, PREG_SET_ORDER) ){

			$parentProduct = $this->getTemplateProduct()->getProduct();
			$childProducts = $this->getTemplateProduct()->getChildsProducts();

			foreach( $matches as $match ){

				$replacedValue = '';

				foreach( $childProducts as $childProduct ){
					$this->getTemplateProduct()->setProduct($childProduct, TRUE);
					$replacedValue .= $this->parseData($match[1]);
				}

				$string = str_replace($match[0], $replacedValue, $string);

			}

			$this->getTemplateProduct()->setProduct($parentProduct);

		}

		// Conditional - DEPEND
		$dependPattern = '/{{?DEPEND\s*(.*?)}?}(.*?){{?\\/DEPEND\s*}?}/is';
		$string = $this->parseReplaceData($dependPattern, $string, TRUE);

		// Conditional - IF
		$ifPattern = '/{{?IF\s*(.*?)}?}(.*?){{?\\/IF\s*}?}/is';
		$string = $this->parseReplaceData($ifPattern, $string, TRUE);

		// Conditional - IFNOT
		$ifNotPattern = '/{{?IFNOT\s*(.*?)}?}(.*?){{?\\/IFNOT\s*}?}/is';
		$string = $this->parseReplaceData($ifNotPattern, $string, FALSE);

		// Variables
		$string = $this->getData($string);

		return $string;
	}

	/**
	 * Retrieve variable data
	 * @param string $string
	 * @return string
	 */
	public function getData($string){

		$parses = $this->getParses();

		// REGEX
		// {{ and }} are optional
		// (([a-z0-9_]+)(\((.*?)\))?) = function
		// ((\s\|\s([a-z0-9]+)(\((.*?)\))?)?) = filter
		$regexFunction = '(([a-z0-9_]+)(\((.*?)\))?)';
		$regexFilter = '((\s\|\s([a-z0-9]+)(\((.*?)\))?)?)';

		// RESULT (Array)
		// 0 = match
		// 1 = function match
		// 2 = function name
		// 3 = function params match
		// 4 = function params
		// 5 = filter definition
		// 6, 7, 8, ... = filter matchs, not used
		foreach( $parses as $parse => $function ){
			$string = preg_replace_callback(
				'/{?{?'. $parse. '::'. $regexFunction. '('. $regexFilter. '+)?}?}?/is',
				array($this, $function),
				$string
			);
		}

		return $string;
	}

	/**
	 * Retrieve function params data
	 * @param array $matches
	 * @return array
	 */
	public function getFunctionParams($matches){

		$params = array();

		if( isset($matches[4]) AND $matches[4] ){

			$input = explode(',', $matches[4]);

			foreach( $input as $key => $value ){

				$param = str_replace(array('"', "'"), '', trim($value));

				if( $param ){
					$param = $this->getData($param);
				}

				$params[ $key ] = $param;
			}

		}

		return $params;
	}

	/**
	 * Retrieve XML data to replace on XML feed
	 * @param array $matches
	 * @return string
	 */
	public function getXmlData($matches){

		$item = $matches[2];
		$params = $this->getFunctionParams($matches);

		$value = $this->dispatchEvent('xml', array(
			'template' => $this->getTemplateXml(),
			'item' => $item,
			'params' => $params
		));

		if( $value == NULL ){
			$value = $this->getTemplateXml()->getData($item, $params);
		}

		return $this->applyFilterData($value, $matches);
	}

	/**
	 * Retrieve Store data to replace on XML feed
	 * @param array $matches
	 * @return string
	 */
	public function getStoreData($matches){

		$item = $matches[2];
		$params = $this->getFunctionParams($matches);

		$value = $this->dispatchEvent('store', array(
			'template' => $this->getTemplateStore(),
			'item' => $item,
			'params' => $params
		));

		if( $value == NULL ){
			$value = $this->getTemplateStore()->getData($item, $params);
		}

		return $this->applyFilterData($value, $matches);
	}

	/**
	 * Retrieve Product data to replace on XML item object
	 * @param array $matches
	 * @return string
	 */
	public function getProductData($matches){

		$item = $matches[2];
		$params = $this->getFunctionParams($matches);

		$value = $this->dispatchEvent('product', array(
			'template' => $this->getTemplateProduct(),
			'item' => $item,
			'params' => $params
		));

		if( $value == NULL ){
			$value = $this->getTemplateProduct()->getData($item, $params);
		}

		return $this->applyFilterData($value, $matches);
	}

	/**
	 * Retrieve Product childs data to replace on XML item object
	 * @param array $matches
	 * @return string
	 */
	public function getProductChildsData($matches){

		$item = $matches[2];
		$params = $this->getFunctionParams($matches);

		$value = $this->dispatchEvent('product_childs', array(
			'template' => $this->getTemplateProduct(),
			'item' => $item,
			'params' => $params
		));

		if( $value == NULL ){
			$value = $this->getTemplateProduct()->getChildsData($item, $params);
		}

		return $this->applyFilterData($value, $matches);
	}

	/**
	 * Retrieve Product stock data to replace on XML item object
	 * @param array $matches
	 * @return string
	 */
	public function getProductStockData($matches){

		$item = $matches[2];
		$params = $this->getFunctionParams($matches);

		$value = $this->dispatchEvent('product_stock', array(
			'template' => $this->getTemplateProduct(),
			'item' => $item,
			'params' => $params
		));

		if( $value == NULL ){
			$value = $this->getTemplateProduct()->getStockData($item, $params);
		}

		return $this->applyFilterData($value, $matches);
	}

	/**
	 * Retrieve Product categories data to replace on XML item object
	 * @param array $matches
	 * @return string
	 */
	public function getProductCategoriesData($matches){

		if( strpos($matches[0], 'SUBCATEGORY') !== FALSE ){
			$mode = 'SUBCATEGORY';
		}elseif( strpos($matches[0], 'CATEGORY') !== FALSE ){
			$mode = 'CATEGORY';
		}else{
			$mode = 'CATEGORIES';
		}

		$item = $matches[2];
		$params = $this->getFunctionParams($matches);

		$value = $this->dispatchEvent('product_categories', array(
			'template' => $this->getTemplateProduct(),
			'item' => $item,
			'params' => $params
		));

		if( $value == NULL ){
			$value = $this->getTemplateProduct()->getCategoriesData($item, $mode, $params);
		}

		return $this->applyFilterData($value, $matches);
	}

	/**
	 * Retrieve Product attribute data to replace on XML item object
	 * @param array $matches
	 * @return string
	 */
	public function getProductAttributeData($matches){

		$item = $matches[2];
		$params = $this->getFunctionParams($matches);

		$value = $this->dispatchEvent('product_attribute', array(
			'template' => $this->getTemplateProduct(),
			'item' => $item,
			'params' => $params
		));

		if( $value == NULL ){
			$value = $this->getTemplateProduct()->getAttributeData($item, $params);
		}

		return $this->applyFilterData($value, $matches);
	}

	/**
	 * Apply filters to format data
	 * @param mixed $value
	 * @param array $matches
	 * @return mixed
	 */
	public function applyFilterData($value, $matches){

		// RESULT (Array)
		// 0 = match
		// 1 = function match
		// 2 = function name
		// 3 = function params match
		// 4 = function params
		$regex = '/(([a-z_]+)(\((.*?)\))?)/is';

		$filters = ( isset($matches[5]) ) ? $matches[5] : '';
		$filters = explode('|', $filters);

		if( !$filters ){
			$filters = array('escapeData');
		}

		foreach( $filters as $filter ){

			$filter = trim($filter);

			if( preg_match($regex, $filter, $filterMatches) ){

				$filterName = $filterMatches[2];
				$filterParams = $this->getFunctionParams($filterMatches);

				switch ($filterName) {
					case 'to_number':
					case 'to_int':
					case 'toNumber':
					case 'toInt':
						$value = (int) $value;
					break;
					case 'to_price':
					case 'toPrice':
						if($this->getTemplateStore()->getStore()->getStoreId() == 4){
							$value = Mage::helper('core')->currency($value, true, FALSE);
							$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
						} else {
							$value = Mage::helper('core')->currency($value, FALSE, FALSE);
							$value = sprintf('%01.2F', $value);
						}
					break;
					case 'to_price_with_comman':
					case 'toPriceWithComman':
						$value = Mage::helper('core')->currency($value, FALSE, FALSE);
						$value = sprintf('%01.2F', $value);
						$value = str_replace(',', '', $value);
						$value = str_replace('.', ',', $value);
					break;
					case 'to_currency':
					case 'toCurrency':
						$value = Mage::helper('core')->currency($value, TRUE, FALSE);
					break;
					case 'to_output':
					case 'toOutput':
						$product = $this->getTemplateProduct()->getProduct();
						$attribute = $matches[2];
						$value = Mage::helper('catalog/output')
									   ->productAttribute($product, $value, $attribute);
					break;
					case 'escape_html':
					case 'escapeHTML':
						$value = str_replace('&', '&amp;', $value);
						$value = str_replace('<', '&lt;', $value);
						$value = str_replace('>', '&gt;', $value);
						$value = str_replace('"', '&quot;', $value);
						$value = str_replace("'", '&apos;', $value);
					break;
					case 'max_length':
					case 'maxLength':
						$value = mb_substr($value, 0, $filterParams[0], 'UTF-8');
						$value = str_replace(' & ', ' &amp; ', $value);
					break;
					case 'escape_data':
					case 'escapeData':
						$value = str_replace(' & ', ' &amp; ', $value);
					break;
				}

			}
		}

		return $value;
	}

}