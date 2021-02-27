<?php
$installer = $this;

$installer->startSetup();

$tableName = $this->getTable('sales/order');

if(!$installer->getConnection()->tableColumnExists($tableName, 'skyhub_code')){
    $table_engine = $installer->getConnection()->raw_fetchRow("SHOW TABLE STATUS WHERE Name = '".$tableName."' ", 'Engine');
    if($table_engine == "InnoDB") {
        $installer->run("ALTER TABLE `{$tableName}` ADD `skyhub_code` varchar(255) DEFAULT NULL");
        $installer->run('ALTER TABLE ' . $tableName . ' ADD UNIQUE (skyhub_code);');
    }
}

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$codigo = 'skyhub_send';
if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
    $config = array(
        'input'                     => 'select',
        'type'                      => 'int',
        'source'                    => 'eav/entity_attribute_source_boolean',
        'position'                  => 1,
        'required'                  => 0,
        'default'                   => '0',
        'label'                     => 'Enviar para Skyhub',
        'apply_to'                  => 'simple,bundle,grouped,configurable',
        'note'                      => 'Enviar esse produto para Skyhub'
    );
    $setup->addAttribute('catalog_product', $codigo , $config);
}

$installer->endSetup();