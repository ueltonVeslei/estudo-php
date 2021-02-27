<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product {

	/**
	 * Add special attributes
	 * @param array $attributes
	 */
	protected function _addSpecialAttributes(array &$attributes){
		parent::_addSpecialAttributes($attributes);
		$attributes['product_stock'] = Mage::helper('superxmlfeed')->__('Product Stock');
		$attributes['product_type'] = Mage::helper('superxmlfeed')->__('Product Type');
		$attributes['product_visibility'] = Mage::helper('superxmlfeed')->__('Product Visibility');
	}

	/**
	 * Retrieve input type
	 * @return string
	 */
	public function getInputType(){

		$code = $this->getAttribute();

		if( $code === 'product_stock' ){
			return 'select';
		}
		if( $code === 'product_type' ){
			return 'select';
		}
		if( $code === 'product_visibility' ){
			return 'select';
		}

		return parent::getInputType();
	}

	/**
	 * Retrieve value element type
	 * @return string
	 */
	public function getValueElementType(){

		$code = $this->getAttribute();

		if( $code === 'product_stock' ){
			return 'select';
		}
		if( $code === 'product_type' ){
			return 'multiselect';
		}
		if( $code === 'product_visibility' ){
			return 'multiselect';
		}

		return parent::getValueElementType();
	}

	/**
	 * Retrive value options
	 * @return array
	 */
	public function getValueSelectOptions(){

		$code = $this->getAttribute();

		switch( $code ){
			case 'product_stock':
				$options = Mage::getModel('superxmlfeed/source_product_stock')
					->toOptionArray();
			break;
			case 'product_type':
				$options = Mage::getModel('superxmlfeed/source_product_type')
					->toOptionArray();
			break;
			case 'product_visibility':
				$options = Mage::getModel('superxmlfeed/source_product_visibility')
					->toOptionArray();
			break;
			default:
				$options = parent::getValueSelectOptions();
			break;
		}

		return $options;
	}

	/**
	 * Validate product attribute value for condition
	 * @param object $object
	 * @return bool
	 */
	public function validate(Varien_Object $object){

		$code = $this->getAttribute();
		$validate = array('product_stock', 'product_type', 'product_visibility');

		if( !in_array($code, $validate) ){
			return parent::validate($object);
		}

		$operator = $this->getOperatorForValidate();
		$value = $this->getValue();
		$result = FALSE;

		if( $object instanceof Mage_Catalog_Model_Product ){
			$product = $object;

		}else{
			$product = Mage::getModel('catalog/product')
							->setStoreId($object->getStoreId())
							->load($object->getId());
		}

		if( $code == 'product_stock' ){
			$result = $product->getStockItem()->getIsInStock();
			$result = in_array($result, array($value));

		}elseif( $code == 'product_type' ){
			$type = $product->getTypeId();
			$result = in_array($type, array_values($value));

		}elseif( $code == 'product_visibility' ){
			$visibility = $product->getVisibility();
			$result = in_array($visibility, array_values($value));

		}

		if( $operator == '!=' ){
			$result = !$result;
		}

		return (boolean)$result;
	}

}