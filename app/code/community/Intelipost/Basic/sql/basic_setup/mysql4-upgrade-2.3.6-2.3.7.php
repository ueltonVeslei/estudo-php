<?php 

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('basic/methods'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('method_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Method Id')
    ->addColumn('method_description', Varien_Db_Ddl_Table::TYPE_TEXT, 120, array(
        ), 'Method Description');
$installer->getConnection()->createTable($table);
$installer->endSetup();