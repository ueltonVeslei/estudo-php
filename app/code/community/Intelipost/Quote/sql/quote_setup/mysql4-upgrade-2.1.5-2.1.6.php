<?php
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();
// Remove Product Attribute
$installer->removeAttribute('catalog_product', 'intelipost_sub_fora_fg');

$installer->updateAttribute('catalog_product', 'frete_price', 'label', 'Valor Frete Embutido(Intelipost) - Frete Grátis');

$installer->addAttribute('catalog_product', 'intelipost_frete_nofg', array(
  'type'              => 'decimal',
  'backend'           => '',
  'frontend'          => '',
  'label'             => 'Valor Frete Embutido(Intelipost) - Não Frete Grátis',
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
  'visible_on_front'  => false,
  'unique'            => false,
  'group'             => 'prices'
));

$installer->removeAttribute('customer', 'customer_attribute_code');
$installer->endSetup();