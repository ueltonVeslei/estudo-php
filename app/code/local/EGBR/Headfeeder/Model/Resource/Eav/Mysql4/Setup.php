<?php
/*
class EGBR_Headfeeder_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
*/
	/**
	* @return array
	*/
/*
	public function getDefaultEntities(){
                Mage::log('Initializing getDefaultEntities();');
		return array(
			'catalog_product' => array(
				'entity_model'      => 'catalog/product',
				'attribute_model'   => 'catalog/resource_eav_attribute',
				'table'             => 'catalog/product',
				'additional_attribute_table' => 'catalog/eav_attribute',
				'entity_attribute_collection' => 'catalog/product_attribute_collection',
				'attributes'        => array(
					'feed_status' => array(
						'group'             => 'General',
						'label'             => 'feed_status',
						'type'              => 'int',
						'input'             => 'boolean',
						'default'           => '0',
						'class'             => '',
						'backend'           => '',
						'frontend'          => '',
						'source'            => '',
						'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
						'visible'           => false,
						'required'          => true,
						'user_defined'      => false,
						'searchable'        => false,
						'filterable'        => false,
						'comparable'        => false,
						'visible_on_front'  => false,
						'visible_in_advanced_search' => false,
						'unique'            => false
					),
				)
			),
			// define attributes for other model entities here
		);
	}
	
	public function createNewAttributeSet($name) {
                Mage::log('Initializing createNewAttributeSet();');
		Mage::app('default');
		$modelSet = Mage::getModel('eav/entity_attribute_set')
			->setEntityTypeId(4) // 4 == "catalog/product"
			->setAttributeSetName($name);
		$modelSet->save();         
		$modelSet->initFromSkeleton(4)->save(); // same thing
	}

}
*/