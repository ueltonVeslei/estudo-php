<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('order', 'ov_autorizacao', array());
$installer->addAttribute('order', 'ov_cotacao', array());
$installer->addAttribute('order', 'ov_origem', array());
$installer->addAttribute('order', 'ov_referencia', array());
$installer->addAttribute('order', 'ov_notafiscal', array());

$tableName = $this->getTable('sales/order');

if(!$installer->getConnection()->tableColumnExists($tableName, 'roche_code')){
    $table_engine = $installer->getConnection()->raw_fetchRow("SHOW TABLE STATUS WHERE Name = '".$tableName."' ", 'Engine');
    if($table_engine == "InnoDB") {
        $installer->run("ALTER TABLE `{$tableName}` ADD `roche_code` varchar(255) DEFAULT NULL");
        $installer->run('ALTER TABLE ' . $tableName . ' ADD UNIQUE (roche_code);');
    }
}

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$codigo = 'roche_send';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
    $config = array(
        'input'                     => 'select',
        'type'                      => 'int',
        'source'                    => 'eav/entity_attribute_source_boolean',
        'position'                  => 1,
        'required'                  => 0,
        'default'                   => '0',
        'label'                     => 'Enviar para Roche',
        'apply_to'                  => 'simple,bundle,grouped,configurable',
        'note'                      => 'Enviar esse produto para Roche'
    );
    $setup->addAttribute('catalog_product', $codigo , $config);
}

$installer->endSetup();