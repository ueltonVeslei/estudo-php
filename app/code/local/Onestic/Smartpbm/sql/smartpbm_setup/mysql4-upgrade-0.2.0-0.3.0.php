<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('smartpbm_quote')} 
	ADD COLUMN `transaction_info` TEXT AFTER `date`;");

$installer->run("ALTER TABLE {$this->getTable('smartpbm_order')} 
	ADD COLUMN `transaction_info` TEXT AFTER `date`;");

$installer->endSetup();