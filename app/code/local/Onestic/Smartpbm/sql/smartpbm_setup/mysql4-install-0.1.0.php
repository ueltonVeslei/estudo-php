<?php
$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$codigo = 'ean';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
    $config = array(
        'position' => 1,
        'required'=> 0,
        'label' => 'EAN',
        'type' => 'text',
        'input'=>'text',
        'apply_to'=>'simple,bundle,grouped,configurable',
        'note'=>'Código EAN do produto'
    );
    $setup->addAttribute('catalog_product', $codigo , $config);
}

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('smartpbm_products')};
    CREATE TABLE {$this->getTable('smartpbm_products')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER,
    `pbm` VARCHAR(20),
    `updated_at` DATETIME,
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('smartpbm_quote')};
    CREATE TABLE {$this->getTable('smartpbm_quote')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `quote_id` INTEGER,
    `pbm` VARCHAR(20),
    `card` VARCHAR(255),
    `ean` VARCHAR(20),
    `product_name` VARCHAR(255),
    `product_id` INTEGER,
    `discount` DECIMAL(9,4),
    `original_price` DECIMAL(8,4),
    `finalprice` DECIMAL(8,4),
    `qty` DECIMAL(9,4),
    `date` DATETIME
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('smartpbm_order')};
    CREATE TABLE {$this->getTable('smartpbm_order')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INTEGER,
    `order_increment_id` VARCHAR(50),
    `pbm` VARCHAR(20),
    `card` VARCHAR(255),
    `customer` VARCHAR(255),
    `ean` VARCHAR(20),
    `product_name` VARCHAR(255),
    `product_id` INTEGER,
    `discount` DECIMAL(9,4),
    `original_price` DECIMAL(9,4),
    `finalprice` DECIMAL(9,4),
    `qty` DECIMAL(9,4),
    `date` DATETIME,
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();