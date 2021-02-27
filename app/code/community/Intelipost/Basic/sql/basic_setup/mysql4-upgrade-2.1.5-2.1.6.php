<?php

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();

$installer->addAttribute('catalog_product', 'intelipost_peso', array(
  'type'              => 'decimal',
  'backend'           => '',
  'frontend'          => '',
  'label'             => 'Peso - Intelipost',
  'input'             => 'text',
  'class'             => '',  
  'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
  'visible'           => true,
  'required'          => false,
  'user_defined'      => false,
  'default'           => '',
  'searchable'        => false,
  'filterable'        => false,
  'comparable'        => false,
  'visible_on_front'  => false,
  'unique'            => false,
  'group'             => 'Intelipost'
));

$installer->endSetup();