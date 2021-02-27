<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('smartpbm_quote')} 
	ADD COLUMN `status` TINYINT AFTER `quote_id`;");

$installer->endSetup();