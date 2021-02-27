<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Rule_Condition_Combine extends Mage_CatalogRule_Model_Rule_Condition_Combine {

	/**
	 * CONSTRUCTOR
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		$this->setType('superxmlfeed/rule_condition_combine');
	}

	/**
	 * Retrieve child selector options
	 * @return array
	 */
	public function getNewChildSelectOptions(){

		$productCondition = Mage::getModel('superxmlfeed/rule_condition_product');
		$productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
		$attributes = array();

		foreach( $productAttributes as $code => $label ){
			$attributes[] = array(
				'value' => 'superxmlfeed/rule_condition_product|'.$code,
				'label' => $label
			);
		}

		$conditions = Mage_Rule_Model_Condition_Combine::getNewChildSelectOptions();
		$conditions = array_merge_recursive($conditions, array(
			array(
				'value' => 'superxmlfeed/rule_condition_combine',
				'label' => Mage::helper('superxmlfeed')->__('Conditions Combination')
			),
			array(
				'label' =>Mage::helper('superxmlfeed')->__('Product Attribute'),
				'value' => $attributes
			)
		));

		return $conditions;
	}

}