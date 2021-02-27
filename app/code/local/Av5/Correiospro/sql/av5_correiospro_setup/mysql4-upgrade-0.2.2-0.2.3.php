<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$codigo = 'servicos_correios';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
	$config = array( 
		'label' => 'Serviços Correios', 
		'type' => 'varchar', 
		'input' => 'multiselect', 
		'backend' => 'eav/entity_attribute_backend_array', 
		'frontend' => '', 
		'source' => 'av5_correiospro/source_availables', 
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL, 
		'visible' => true, 
		'required' => false, 
		'user_defined' => false, 
		'searchable' => false, 
		'filterable' => false, 
		'comparable' => false, 
		'visible_on_front' => false, 
		'visible_in_advanced_search' => false, 
		'unique' => false 
	);
	
	$setup->addAttribute('catalog_product', $codigo, $config);
}

$installer->endSetup();