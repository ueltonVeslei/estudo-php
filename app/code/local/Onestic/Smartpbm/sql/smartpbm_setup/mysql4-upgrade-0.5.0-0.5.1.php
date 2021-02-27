<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('smartpbm_products')} 
	ADD COLUMN `program` VARCHAR(255) AFTER `ean`;");

$installer->run("ALTER TABLE {$this->getTable('smartpbm_products')} 
	ADD COLUMN `program_code` INT AFTER `program`;");

$installer->endSetup();