<?php

// Axado_Shipping_Model_Resource_Setup
$installer = $this;
$installer->startSetup();

/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// Add volume to prduct attribute set
$codigo = 'volume_comprimento';
$config = array(
    'position' => 1,
    'required' => 0,
    'label'    => 'Comprimento (cm)',
    'type'     => 'int',
    'input'    => 'text',
    'apply_to' => 'simple,bundle,grouped,configurable',
    'note'     => 'Comprimento da embalagem do produto'
);

$setup->addAttribute('catalog_product', $codigo, $config);

// Add volume to prduct attribute set
$codigo = 'volume_altura';
$config = array(
    'position' => 1,
    'required' => 0,
    'label'    => 'Altura (cm)',
    'type'     => 'int',
    'input'    => 'text',
    'apply_to' => 'simple,bundle,grouped,configurable',
    'note'     => 'Altura da embalagem do produto'
);

$setup->addAttribute('catalog_product', $codigo, $config);

// Add volume to prduct attribute set
$codigo = 'volume_largura';
$config = array(
    'position' => 1,
    'required' => 0,
    'label'    => 'Largura (cm)',
    'type'     => 'int',
    'input'    => 'text',
    'apply_to' => 'simple,bundle,grouped,configurable',
    'note'     => 'Largura da embalagem do produto'
);

$setup->addAttribute('catalog_product', $codigo, $config);

// Add column to axado_token
$installer->run("ALTER TABLE sales_flat_quote_shipping_rate ADD COLUMN axado_token VARCHAR(255);");

$installer->endSetup();
