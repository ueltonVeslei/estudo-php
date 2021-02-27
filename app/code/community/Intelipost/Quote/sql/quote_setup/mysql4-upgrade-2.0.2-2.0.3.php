<?php

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();
$installer->addAttribute('catalog_product', 'prazo_produto', array(
  'type'              => 'int',
  'backend'           => '',
  'frontend'          => '',
  'label'             => 'Intelipost Prazo do Produto',
  'input'             => 'text',
  'class'             => '',  
  'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
  'visible'           => true,
  'required'          => false,
  'user_defined'      => false,
  'default'           => 0,
  'searchable'        => false,
  'filterable'        => false,
  'comparable'        => false,
  'visible_on_front'  => true,
  'unique'            => false,
  'group'             => 'general'
));

$installer->endSetup();