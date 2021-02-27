<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

# Inclui coluna tipo_prazo na tabela de promoções
$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')} 
	ADD COLUMN `store_id` INTEGER AFTER `id`;");
$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')}
	ADD COLUMN `website_id` INTEGER AFTER `store_id`;");
$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')}
	ADD COLUMN `produtos` TEXT AFTER `status`;");
$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')}
	ADD COLUMN `categorias` TEXT AFTER `produtos`;");

$installer->run("ALTER TABLE {$this->getTable('tabela_correiospro')}
	ADD COLUMN `store_id` INTEGER AFTER `id`;");
$installer->run("ALTER TABLE {$this->getTable('tabela_correiospro')}
	ADD COLUMN `website_id` INTEGER AFTER `store_id`;");
$installer->run("ALTER TABLE {$this->getTable('tabela_correiospro')}
	ADD COLUMN `areas_risco` TEXT AFTER `cep_destino_ref`;");

$installer->endSetup();