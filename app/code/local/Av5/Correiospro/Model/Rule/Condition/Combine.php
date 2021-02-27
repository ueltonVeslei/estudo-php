<?php
class Av5_Correiospro_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine {

	/**
	 * CONSTRUCTOR
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		$this->setType('av5_correiospro/rule_condition_combine');
	}

	/**
	 * Retrieve child selector options
	 * @return array
	 */
	public function getNewChildSelectOptions(){

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
        		array('value'=>'salesrule/rule_condition_address|postcode', 'label'=>Mage::helper('salesrule')->__('Shipping Postcode')),
        		array('value'=>'av5_correiospro/rule_condition_product_found', 'label'=>Mage::helper('salesrule')->__('Seleção de Produtos')),
        		array('value'=>'av5_correiospro/rule_condition_combine', 'label'=>Mage::helper('salesrule')->__('Conditions combination')),
        ));
        
        $additional = new Varien_Object();
        Mage::dispatchEvent('salesrule_rule_condition_combine', array('additional' => $additional));
        if ($additionalConditions = $additional->getConditions()) {
        	$conditions = array_merge_recursive($conditions, $additionalConditions);
        }
        
        return $conditions;
	}

}