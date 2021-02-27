<?php

$installer = $this;
$installer->startSetup ();

$table = $installer->getTable ('sales_flat_quote_shipping_rate');

    $installer->getConnection ()
        ->addColumn ($table, 'intelipost_estimated_delivery_business_days', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'nullable' => false,
            'unsigned' => true,
            'comment'  => 'Intelipost Estimated Delivery Business Days'
        	));

   $installer->endSetup();

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();

$installer->addAttribute('catalog_product', 'intelipost_altura', array(
  'type'              => 'decimal',
  'backend'           => '',
  'frontend'          => '',
  'label'             => 'Altura (cm) - Intelipost',
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

$installer->addAttribute('catalog_product', 'intelipost_largura', array(
  'type'              => 'decimal',
  'backend'           => '',
  'frontend'          => '',
  'label'             => 'Largura (cm) - Intelipost',
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

$installer->addAttribute('catalog_product', 'intelipost_comprimento', array(
  'type'              => 'decimal',
  'backend'           => '',
  'frontend'          => '',
  'label'             => 'Comprimento (cm) - Intelipost',
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