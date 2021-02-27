<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

# Inclui coluna tipo_prazo na tabela de promoções
$installer->run("ALTER TABLE {$this->getTable('promos_correiospro')} 
	ADD COLUMN `tipo_prazo` TINYINT AFTER `prazo`;");

$installer->endSetup();