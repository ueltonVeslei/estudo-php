<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('roche_orders')};
    CREATE TABLE {$this->getTable('roche_orders')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(100) NOT NULL,
    `order_id` INTEGER,
    `order_data` TEXT,
    `increment_id` VARCHAR(20),
    `name` VARCHAR(255),
    `status_sync` VARCHAR(10),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

