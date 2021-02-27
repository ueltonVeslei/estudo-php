<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Template_Product{

	/**
	 * Store ID
	 * @var int
	 */
	private $_storeId;

	/**
	 * Product object
	 * @var object
	 */
	private $_product;

	/**
	 * Child products
	 * @var array
	 */
	private $_childProducts = array();

	/**
	 * Special product types
	 * @var array
	 */
	private $_specialProductTypes = array(
		'bundle',
		'configurable',
		'grouped'
	);

	/**
	 * Set Store ID
	 * @return void
	 */
	public function setStoreId($storeId){
		$this->_storeId = $storeId;
	}

	/**
	 * Retrieve Store ID
	 * @return int
	 */
	public function getStoreId(){
		return $this->_storeId;
	}

	/**
	 * Set Product
	 * @param object|int $product
	 * @param boolean $force
	 * @return void
	 */
	public function setProduct($product, $force = FALSE){

		if( is_object($product) AND !$force ){
			$this->_product = $product;
			return;
		}

		if( is_object($product) ){
			$product = $product->getId();
		}

		$this->_product = Mage::getModel('catalog/product')
								->setStoreId( $this->getStoreId() )
								->load($product);

	}

	/**
	 * Get Product
	 * @return object
	 */
	public function getProduct(){
		return $this->_product;
	}

	/**
	 * Reset child products data
	 * @return void
	 */
	public function resetChildProducts(){
		$this->_childProducts = array();
	}

	/**
	 * Retrieve childs product for special product types
	 * @return object
	 */
	public function getChildsProducts(){

		if( !$this->_childProducts ){

			$childProducts = array();
			$typeId = $this->getProduct()->getTypeId();

			if( $typeId == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ){
				$childProducts = $this->getProduct()
									->getTypeInstance(true)
									->getUsedProducts(null, $this->getProduct());

			}elseif( $typeId == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ){
				$childProducts = $this->getProduct()
									->getTypeInstance(true)
									->getAssociatedProducts($this->getProduct());

			}elseif( $typeId == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ){
				$childProductsIds = $this->getProduct()
									->getTypeInstance(true)
									->getChildrenIds($this->getProduct()->getId(), FALSE);

				foreach( $childProductsIds as $optionKey => $optionData ){
					$childProducts = array_merge($childProducts, $optionData);
				}

			}

			$this->_childProducts = $childProducts;
		}

		return $this->_childProducts;
	}

	/**
	 * Get product data
	 * @param string $item
	 * @param array $params
	 * @return string
	 */
	public function getData($item, $params = array()){

		$product = $this->getProduct();
		$value = '';

		switch( $item ){
			case 'id':
				$value = $product->getId();
			break;
			case 'title':
				$value = $product->getName();
			break;
			case 'price':
			case 'final_price':
			case 'finalPrice':
				$value = $this->getPriceData('price', $params);
			break;
			case 'normal_price':
			case 'normalPrice':
				$value = $this->getPriceData('normal_price', $params);
			break;
			case 'special_price':
			case 'specialPrice':
				$value = $this->getPriceData('special_price', $params);
			break;
			case 'price_with_math':
			case 'priceWithMath':
				$value = $this->getPriceDataWithMath($item, $params);
			break;
			case 'installment_price':
			case 'installment_times':
			case 'installment_total':
			case 'installmentPrice':
			case 'installmentTimes':
			case 'installmentTotal':
				$value = $this->getInstallmentsData($item, $params);
			break;
			case 'condition':
				$value = ( $product->getData('condition') )
						 ? $product->getData('condition') : 'new';
			break;
			case 'product_url':
			case 'productUrl':
				$value = $product->getProductUrl();
			break;
			case 'url':
			case 'link':
				$value = Mage::app()->getStore( $this->getStoreId() )
						->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).
						$product->getData('url_path');
			break;
			case 'image':
			case 'thumbnail':
			case 'small_image':
			case 'smallImage':
				$value = $this->getImageData($item, $params);
			break;
			case 'image_url':
			case 'imageUrl':

				if( !isset($params[0]) ) $params[0] = 'image';
				if( !isset($params[1]) ) $params[1] = null;
				if( !isset($params[2]) ) $params[2] = null;

				$value = $this->getCustomImageData($params[0], $params[1], $params[2]);

			break;
			case 'type':
				$value = $product->getTypeId();
			break;
			case 'is_product_type':
			case 'isProductType':

				if( !isset($params[0]) ) $params[0] = null;

				$value = ( $product->getTypeId() == $params[0] ) ? TRUE : FALSE;

			break;
			case 'weight':

				$value = $product->getData($item);

				if( !$value AND in_array($product->getTypeId(), $this->_specialProductTypes) ){
					$value = $this->getFirstChildData($item, $params);
				}

			break;
			default:
				$value = $product->getData($item);
			break;
		}

		return $value;
	}

	/**
	 * Retrieve first available data from child product
	 * @param string $item
	 * @param array $params
	 * @return mixed
	 */
	public function getFirstChildData($item, $params = array()){

		$parentProduct = $this->getProduct();
		$childProducts = $this->getChildsProducts();

		$value = '';

		if( !count($childProducts) ){
			return $value;
		}

		foreach( $childProducts as $childProduct ){

			$this->setProduct($childProduct, TRUE);
			$value = $this->getData($item, $params);

			if( $value ){
				break;
			}
		}

		$this->setProduct($parentProduct);

		return $value;
	}

	/**
	 * Get childs products data
	 * @param string $item
	 * @param array $params
	 * @return string
	 */
	public function getChildsData($item, $params = array()){

		$values = array();
		$separator = ( isset($params[1]) AND $params[1] ) ? $params[1] : ', ';

		$parentProduct = $this->getProduct();
		$childProducts = $this->getChildsProducts();

		foreach( $childProducts as $childProduct ){

			$this->setProduct($childProduct, TRUE);

			$product = $this->getProduct();
			$subvalue = '';

			switch( $item ){
				case 'attribute':
					$subvalue = $this->getAttributeData($params[0]);
				break;
				default:
					$subvalue = $this->getData($item, $params);
				break;
			}

			if( $subvalue AND !in_array($subvalue, $values) ){
				$values[] = $subvalue;
			}

		}

		$this->setProduct($parentProduct);

		$value = implode($separator, $values);

		return $value;
	}

	/**
	 * Get product price data
	 * If price in not present on parent product, try to find price on child products
	 * @param string $item
	 * @param array $params
	 * @return string
	 */
	public function getPriceData($item = 'price', $params = array()){

		$product = $this->getProduct();

		switch( $item ){
			case 'price':
			case 'final_price':
			case 'finalPrice':
				$value = $product->getFinalPrice();
			break;
			case 'normal_price':
			case 'normalPrice':
				$value = $product->getPrice();
			break;
			case 'special_price':
			case 'specialPrice':
				$value = $product->getSpecialPrice();
			break;
		}

		if( !$value AND in_array($product->getTypeId(), $this->_specialProductTypes) ){
			$value = $this->getFirstChildData($item, $params);
		}

		return $value;
	}

	/**
	 * Get product price data with mathematics processing
	 * @param string $item
	 * @param array $params
	 * @return string
	 */
	public function getPriceDataWithMath($item, $params = array()){
	
		$total = $this->getPriceData('price', $params);
		$math = $params[0];
		$mathValue = $params[1];

		$isMinus = ( strpos($mathValue, '-') !== FALSE ) ? TRUE : FALSE;
		$isPercent = ( strpos($mathValue, '%') !== FALSE ) ? TRUE : FALSE;
		$mathValue = (float) str_replace(array('-', '%'), '', $mathValue);

		if( $isPercent ){
			$plus = ($total/100) * $mathValue;
		}else{
			$plus = $mathValue;
		}

		if( $isMinus ){
			$plus = -$plus;
		}

		$value = ( $math == 'increase' ) ? $total + $plus : $total - $plus;

		return $value;
	}

	/**
	 * Get product price installment data
	 * @param string $item
	 * @param array $params
	 * @return string
	 */
	public function getInstallmentsData($item, $params = array()){

		$price = $this->getPriceData('price', $params);
		$availableOptions = array();
		
		$installments = (int) $params[0];
		$interest = (float) $params[1];
		$minimum = ( isset($params[2]) AND $params[2] ) ? (float) $params[2] : 0;
		$calculation = ( isset($params[3]) AND $params[3] == 'composed' ) ? 'composed' : 'simple';

		for( $i = 1; $i <= $installments; $i++ ){ 

			$times = (int) $i;
			$tax = $interest / 100;
			$total = $price;
			
			// Simple fee method
			if( $times > 1 AND $calculation == 'simple' ){
 	        	$total = $price * (1 + $tax * $times);

			// Composed fee method
			}elseif( $times > 1 ){
        		$total = $price * pow((1 + $tax), $times);
			}

			$value = $total / $times;

			if( $times > 1 AND $value < $minimum ){
				continue;
			}

			$availableOptions[ $times ] = array(
				'times' => $times,
				'value' => $value,
				'total' => $total,
				'minimum' => $minimum,
				'interest' => $interest
			);

		}

		$last = $availableOptions[ count($availableOptions) ];

		switch( $item ){
			case 'installment_times':
			case 'installmentTimes':
				$value = $last['times'];
			break;
			case 'installment_price':
			case 'installmentPrice':
				$value = $last['value'];
			break;
			case 'installmentTotal':
			case 'installment_total':
				$value = $last['total'];
			break;
		}

		return $value;
	}

	/**
	 * Get product image data
	 * @param string $type
	 * @param array $params
	 * @return string
	 */
	public function getImageData($type = 'image', $params = array()){

		$product = $this->getProduct();
		$value = '';

		try{

			switch( $type ){
				case 'image':
					$value = $product->getImageUrl();
				break;
				case 'thumbnail':
					$value = $product->getThumbnailUrl();
				break;
				case 'small_image':
				case 'smallImage':
					$value = $product->getSmallImageUrl();
				break;
			}

		}catch( Exception $e ){

		}

		if( !$value ){
			$value = Mage::getDesign()->getSkinUrl(
				'images/catalog/product/placeholder/image.jpg',
				array('_area' => 'frontend')
			);
		}

		return $value;
	}

	/**
	 * Get product custom image data
	 * @param string $type
	 * @param int $width
	 * @param int $height
	 * @return string
	 */
	public function getCustomImageData($type = 'image', $width = null, $height = null){

		$product = $this->getProduct();
		$value = '';

		if( $width AND !$height ){
			$height = $width;
		}

		try{

			$image = Mage::helper('catalog/image')->init($product, $type);

			if( $width ){
				$image->resize( (int) $width, (int) $height );
			}

			$value = (string) $image;

		}catch( Exception $e ){

		}

		if( !$value ){
			$value = Mage::getDesign()->getSkinUrl(
				'images/catalog/product/placeholder/image.jpg',
				array('_area' => 'frontend')
			);
		}

		return $value;
	}

	/**
	 * Get product stock data
	 * @param string $item
	 * @param array $params
	 * @return string
	 */
	public function getStockData($item, $params = array()){

		$value = '';
		$product = $this->getProduct();
		$stock = $product->getStockItem();

		switch( $item ){
			case 'qty':

				if( in_array($product->getTypeId(), $this->_specialProductTypes) ){
					$value = $this->getChildsStockData($item, $params);
				}else{
					$value = $stock->getData('qty');
				}

			break;
			case 'availability':

				if( $stock->getIsInStock() ){
					$value = 'in stock';
				}else{
					$value = ( $stock->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_NO ) ? 'out of stock' : 'preorder';
				}

			break;
			default:
				$value = $stock->getData($item);
			break;
		}

		return $value;
	}

	/**
	 * Get childs products stock data
	 * This function also sum all stock data in special cases
	 * @param string $item
	 * @param array $params
	 * @return mixed
	 */
	public function getChildsStockData($item, $params = array()){

		$values = array();
		$separator = ( isset($params[0]) AND $params[0] ) ? $params[0] : ', ';

		$parentProduct = $this->getProduct();
		$childProducts = $this->getChildsProducts();

		foreach( $childProducts as $childProduct ){

			$this->setProduct($childProduct, TRUE);
			$subvalue = $this->getStockData($item, $params);

			$values[] = $subvalue;

		}

		$this->setProduct($parentProduct);

		if( $item == 'qty' ){
			$value = array_sum($values);
		}else{
			$value = implode($separator, $values);
		}

		return $value;
	}

	/**
	 * Get product categories data
	 * @param string $item
	 * @param string $mode
	 * @param array $params
	 * @return string
	 */
	public function getCategoriesData($item, $mode, $params = array()){

		$value = '';
		$product = $this->getProduct();
		$helper = Mage::helper('superxmlfeed');

		$rootCategoryId = Mage::app()
							->getStore( $this->getStoreId() )
							->getRootCategoryId();

		$categoriesIds = $product->getCategoryIds();
		$key = array_search($rootCategoryId, $categoriesIds);

		if( $key !== FALSE ){
			unset($categoriesIds[$key]);
			$categoriesIds = array_values($categoriesIds);
		}

		switch( $item ){
			case 'asList':

				$result = array();
				$separator = ($params[0]) ? $params[0] : ', ';

				foreach( $categoriesIds as $categoryId ){
					$category = $helper->loadCategory($categoryId, $this->getStoreId());
					$result[] = (string) $category->getName();
				}

				$value = implode($separator, $result);

			break;
			default:

				if( $mode == 'SUBCATEGORY' ){
					$id = ( isset($categoriesIds[1]) ) ? $categoriesIds[1] : FALSE;
				}else{
					$id = ( isset($categoriesIds[0]) ) ? $categoriesIds[0] : FALSE;
				}

				if( $id ){
					$category = $helper->loadCategory($id, $this->getStoreId());

					if( $item == 'id' ){
						$value = $category->getId();

					}elseif( $item == 'url' ){
						$value = $category->getUrl();

					}else{
						$value = $category->getData($item);
					}
				}

			break;
		}

		return $value;
	}

	/**
	 * Get product attribute data
	 * @param string $item
	 * @param array $params
	 * @return string
	 */
	public function getAttributeData($item, $params = array()){

		$product = $this->getProduct();
		$attribute = $product->getResource()->getAttribute($item);
		$value = '';

		if( $attribute AND $attribute->getId() ){
			$value = $attribute->getFrontend()->getValue($product);
		}

		return $value;
	}

}