<?php

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();
$installer->addAttribute('catalog_product', 'frete_price', array(
  'type'              => 'decimal',
  'backend'           => '',
  'frontend'          => '',
  'label'             => 'Valor Frete Embutido',
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
  'visible_on_front'  => true,
  'unique'            => false,
  'group'             => 'prices'
));

$installer->endSetup();
