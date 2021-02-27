<?php 

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('quote/quote_address_shipping_rate'))
    ->addColumn('rate_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rate Id')
    ->addColumn('address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Address Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Code')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Description')
    ->addColumn('method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Method')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Price')
    ->addColumn ('intelipost_quote_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            'comment'  => 'Intelipost Quote ID',
        ), 'Intelipost Quote Id')
    ->addColumn ('intelipost_estimated_delivery_business_days', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            'comment'  => 'Intelipost Estimated Delivery Business Day',
        ), 'Intelipost Estimated Delivery Business Day')
    ->addColumn ('intelipost_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'  => false,
            'default'   => '0.0000',
            ), 'Shipping Cost')

    ->setComment('Intelipost Flat Quote Shipping Rate');
$installer->getConnection()->createTable($table);
$installer->endSetup();