<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('smartpbm_products')} 
	ADD COLUMN `discount` DECIMAL(9,2) AFTER `pbm`;");

$installer->run("ALTER TABLE {$this->getTable('smartpbm_products')} 
	ADD COLUMN `max_price` DECIMAL(9,2) AFTER `pbm`;");

$installer->run("ALTER TABLE {$this->getTable('smartpbm_products')} 
	ADD COLUMN `ean` VARCHAR(100) AFTER `pbm`;");

$installer->endSetup();