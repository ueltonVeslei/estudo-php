<?php

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();
$installer->addAttribute('catalog_product', 'intelipost_sub_fora_fg', array(
  'type'              => 'int',
  'backend'           => '',
  'frontend'          => '',
  'label'             => 'Subsidio Fora de Frete Grátis(Intelipost)',
  'input'             => 'select',
  'class'             => '',  
  'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
  'visible'           => true,
  'required'          => false,
  'user_defined'      => false,
  'default'           => 0,
  'source'            => 'eav/entity_attribute_source_boolean',
  'searchable'        => false,
  'filterable'        => false,
  'comparable'        => false,
  'visible_on_front'  => true,
  'unique'            => false,
  'group'             => 'prices'
));

$installer->endSetup();