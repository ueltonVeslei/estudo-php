<?php
$installer = $this;

$installer->startSetup();

$tableName = $this->getTable('sales/order');

if(!$installer->getConnection()->tableColumnExists($tableName, 'skyhub_status')){
    $table_engine = $installer->getConnection()->raw_fetchRow("SHOW TABLE STATUS WHERE Name = '".$tableName."' ", 'Engine');
    if($table_engine == "InnoDB") {
        $installer->run("ALTER TABLE `{$tableName}` ADD `skyhub_status` TINYINT DEFAULT NULL");
    }
}
