<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('skyhub_products')};
    CREATE TABLE {$this->getTable('skyhub_products')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `sku` VARCHAR(100) NOT NULL,
    `product_id` INTEGER,
    `name` VARCHAR(255),
    `qty` INTEGER,
    `price` DECIMAL(9,2),
    `promotional_price` DECIMAL(9,2),
    `status` VARCHAR(10),
    `removed` VARCHAR(5) DEFAULT 'NÃO',
    `status_sync` VARCHAR(5) DEFAULT 'NÃO',
    `updated_at` DATETIME,
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

