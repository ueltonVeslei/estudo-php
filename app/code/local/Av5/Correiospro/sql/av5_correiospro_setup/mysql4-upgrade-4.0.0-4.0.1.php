<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')} 
	ADD COLUMN `prioridade` INTEGER AFTER `pedido`;");

$installer->endSetup();