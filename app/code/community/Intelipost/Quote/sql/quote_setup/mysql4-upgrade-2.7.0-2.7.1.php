<?php 

$installer = $this;
$installer->startSetup();

$table = $installer->getTable ('intelipost_quote_shipping_rate');

$installer->getConnection()
    ->changeColumn($table,
    'intelipost_quote_id',
    'intelipost_quote_id',
    array(
            'type'     => Varien_Db_Ddl_Table::TYPE_BIGINT,
            'length'   => 20,
            'nullable' => false,
            'unsigned' => true,
            'comment'  => 'Intelipost Quote ID'
    ));

$installer->endSetup();