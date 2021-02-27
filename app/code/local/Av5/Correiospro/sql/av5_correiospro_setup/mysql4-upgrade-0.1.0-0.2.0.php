<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('promos_correiospro')};
CREATE TABLE {$this->getTable('promos_correiospro')} (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255),
  `servico` VARCHAR(50),
  `localidade` VARCHAR(150) NOT NULL,
  `cep_origem` VARCHAR(20),
  `cep_destino` VARCHAR(20),
  `valor` DECIMAL(8,2),
  `tipo_desconto` TINYINT,
  `prazo` INTEGER,
  `pedido` DECIMAL(8,2),
  `gratis` TINYINT UNSIGNED,
  `status` TINYINT UNSIGNED,
  PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();