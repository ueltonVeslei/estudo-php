<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')} 
	ADD COLUMN `conditions_serialized` TEXT AFTER `pedido`;");

$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')}
	ADD COLUMN `tipo_pedido` TEXT AFTER `pedido_maximo`;");

$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')}
	DROP COLUMN `localidade`;");

$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')}
	DROP COLUMN `cep_origem`;");

$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')}
	DROP COLUMN `cep_destino`;");

$installer->endSetup();