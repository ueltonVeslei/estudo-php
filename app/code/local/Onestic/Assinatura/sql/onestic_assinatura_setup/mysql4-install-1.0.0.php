<?php
/**
** CRIAÇÃO DAS TABELAS NO BANCO DE DADOS
**/

$installer = $this;
$installer->startSetup();

/* Tabela do perfil de recorrência */
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('onestic_assinatura_profile')};
    CREATE TABLE {$this->getTable('onestic_assinatura_profile')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER,
    `discount` DECIMAL(9,4),
    `discount_type` TINYINT,
    `months` INTEGER,
    `status` VARCHAR(20),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

/* Tabela de controle das assinaturas */
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('onestic_assinatura_signature')};
    CREATE TABLE {$this->getTable('onestic_assinatura_signature')} (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER,
    `order_id` INTEGER,
    `profile_id` INTEGER,
    `parcel` INTEGER,
    `months` INTEGER,
    `status` VARCHAR(20),
    `due_date` DATETIME,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$setup = new Mage_Sales_Model_Mysql4_Setup('core_setup');
$setup->addAttribute('order', 'is_recurring', array
(
    'label' => 'Pedido de Assinatura',
    'type'  => 'int',
));
$setup->addAttribute('quote', 'is_recurring', array
(
    'label' => 'Pedido de Assinatura',
    'type'  => 'int',
));

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$codigo = 'assinatura1_meses';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
    $config = array(
        'position' => 1,
        'required'=> 0,
        'label' => 'Plano1 de Assinatura - Meses',
        'type' => 'int',
        'input'=>'text',
        'apply_to'=>'simple,bundle,grouped,configurable',
        'note'=>'Número de meses desse plano de assinatura'
    );
    $setup->addAttribute('catalog_product', $codigo , $config);
}

$codigo = 'assinatura1_desconto';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
    $config = array(
        'position' => 1,
        'required'=> 0,
        'label' => 'Plano1 de Assinatura - Desconto',
        'type' => 'int',
        'input'=>'text',
        'apply_to'=>'simple,bundle,grouped,configurable',
        'note'=>'Percentual de desconto do plano'
    );
    $setup->addAttribute('catalog_product', $codigo , $config);
}

$codigo = 'assinatura2_meses';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
    $config = array(
        'position' => 1,
        'required'=> 0,
        'label' => 'Plano2 de Assinatura - Meses',
        'type' => 'int',
        'input'=>'text',
        'apply_to'=>'simple,bundle,grouped,configurable',
        'note'=>'Número de meses desse plano de assinatura'
    );
    $setup->addAttribute('catalog_product', $codigo , $config);
}

$codigo = 'assinatura2_desconto';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
    $config = array(
        'position' => 1,
        'required'=> 0,
        'label' => 'Plano2 de Assinatura - Desconto',
        'type' => 'int',
        'input'=>'text',
        'apply_to'=>'simple,bundle,grouped,configurable',
        'note'=>'Percentual de desconto do plano'
    );
    $setup->addAttribute('catalog_product', $codigo , $config);
}

$codigo = 'assinatura3_meses';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
    $config = array(
        'position' => 1,
        'required'=> 0,
        'label' => 'Plano3 de Assinatura - Meses',
        'type' => 'int',
        'input'=>'text',
        'apply_to'=>'simple,bundle,grouped,configurable',
        'note'=>'Número de meses desse plano de assinatura'
    );
    $setup->addAttribute('catalog_product', $codigo , $config);
}

$codigo = 'assinatura3_desconto';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
    $config = array(
        'position' => 1,
        'required'=> 0,
        'label' => 'Plano3 de Assinatura - Desconto',
        'type' => 'int',
        'input'=>'text',
        'apply_to'=>'simple,bundle,grouped,configurable',
        'note'=>'Percentual de desconto do plano'
    );
    $setup->addAttribute('catalog_product', $codigo , $config);
}

$installer->endSetup();