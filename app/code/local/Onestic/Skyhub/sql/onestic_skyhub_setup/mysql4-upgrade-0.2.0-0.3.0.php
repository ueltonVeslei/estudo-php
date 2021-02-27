<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('skyhub_orders')};
    CREATE TABLE {$this->getTable('skyhub_orders')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(100) NOT NULL,
    `order_id` INTEGER,
    `increment_id` VARCHAR(20),
    `name` VARCHAR(255),
    `status_skyhub` VARCHAR(20),
    `status_sync` VARCHAR(10),
    `status_invoice_mg` VARCHAR(5) DEFAULT 'NÃO',
    `status_invoice_sh` VARCHAR(5) DEFAULT 'NÃO',
    `status_shipment_mg` VARCHAR(5) DEFAULT 'NÃO',
    `status_shipment_sh` VARCHAR(5) DEFAULT 'NÃO',
    `status_delivery_mg` VARCHAR(5) DEFAULT 'NÃO',
    `status_delivery_sh` VARCHAR(5) DEFAULT 'NÃO',
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

