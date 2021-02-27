<?php
class Av5_Correiospro_Model_Rule_Condition_Product_Combine extends Mage_SalesRule_Model_Rule_Condition_Product_Combine {
    
	public function __construct() {
		parent::__construct();
		$this->setType('av5_correiospro/rule_condition_product_combine');
	}
	
	public function getNewChildSelectOptions()
	{
		$conditions = parent::getNewChildSelectOptions();
		foreach($conditions as $key=>$cond) {
			if (in_array($cond['label'], array(Mage::helper('catalog')->__('Cart Item Attribute'),Mage::helper('catalog')->__('Conditions Combination')))) {
				unset($conditions[$key]);
			}
		}
		$conditions = array_merge_recursive($conditions, array(
				array('value'=>'av5_correiospro/rule_condition_product_combine', 'label'=>Mage::helper('catalog')->__('Conditions Combination')),
				array('value'=>'av5_correiospro/rule_condition_product_found', 'label'=>Mage::helper('av5_correiospro')->__('Seleção de Produtos')),
		));
		
		return $conditions;
	}
	
}
