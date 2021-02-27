<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('performa_queue')};
    CREATE TABLE {$this->getTable('performa_queue')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `sku` VARCHAR(100) NOT NULL,
    `product_id` INTEGER,
    `name` VARCHAR(255),
    `updated_at` DATETIME,
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

