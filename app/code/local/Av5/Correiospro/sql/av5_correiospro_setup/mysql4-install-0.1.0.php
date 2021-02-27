<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('tabela_correiospro')};
CREATE TABLE {$this->getTable('tabela_correiospro')} (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `servico` VARCHAR(50),
  `nome` VARCHAR(50),
  `regiao` VARCHAR(150) NOT NULL,
  `prazo` INTEGER,
  `peso` DECIMAL(8,4),
  `valor` DECIMAL(8,2),
  `cep_origem` VARCHAR(50),
  `cep_destino_ini` INTEGER,
  `cep_destino_fim` INTEGER,
  `lastupdate` DATETIME,
  `cep_destino_ref` INTEGER,
  PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$codigo = 'volume_comprimento';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
	$config = array(
			'position' => 1,
			'required'=> 0,
			'label' => 'Comprimento (cm)',
			'type' => 'int',
			'input'=>'text',
			'apply_to'=>'simple,bundle,grouped,configurable',
			'note'=>'Comprimento da embalagem do produto (Para cálculo dos Correios)'
	);
	$setup->addAttribute('catalog_product', $codigo , $config);
}

$codigo = 'volume_altura';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
	$config = array(
			'position' => 1,
			'required'=> 0,
			'label' => 'Altura (cm)',
			'type' => 'int',
			'input'=>'text',
			'apply_to'=>'simple,bundle,grouped,configurable',
			'note'=>'Altura da embalagem do produto (Para cálculo dos Correios)'
	);
	$setup->addAttribute('catalog_product', $codigo , $config);
}

$codigo = 'volume_largura';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
	$config = array(
			'position' => 1,
			'required'=> 0,
			'label' => 'Largura (cm)',
			'type' => 'int',
			'input'=>'text',
			'apply_to'=>'simple,bundle,grouped,configurable',
			'note'=>'Largura da embalagem do produto (Para cálculo dos Correios)'
	);
	$setup->addAttribute('catalog_product', $codigo , $config);
}

$installer->endSetup();