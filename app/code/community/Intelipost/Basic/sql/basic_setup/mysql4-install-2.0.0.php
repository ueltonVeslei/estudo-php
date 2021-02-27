<?php

$installer = $this;
$installer->startSetup ();

function initTable ($installer, $model_name, $comment, $conditions = null)
{
    $table = $installer->getTable ($model_name);

    $sqlBlock = <<< SQLBLOCK
CREATE TABLE IF NOT EXISTS {$table}
(
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='{$comment}';
SQLBLOCK;

    $installer->run ($sqlBlock);

    return $table;
}

function addOrUpdateOrdersTable ($installer, $model_name, $comment, $conditions = null)
{
    $table = initTable ($installer, $model_name, $comment, $conditions);

    $installer->getConnection ()
        ->addColumn ($table, 'order_id', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Order ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'delivery_quote_id', array(
            'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Delivery Quote ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'delivery_method_id', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Delivery Method ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'status', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'Status',
        ));
    /*
    $now = Mage::getModel ('core/date')->gmtDate ();
    
$sqlBlock = <<< SQLBLOCK
UPDATE {$installer->getTable ($model_name)} SET created_at = '{$now}' WHERE {$conditions};
SQLBLOCK;
    
    $installer->run ($sqlBlock);
    */
}

function addOrUpdateNFEsTable ($installer, $model_name, $comment, $conditions = null)
{
    $table = initTable ($installer, $model_name, $comment, $conditions);

    $installer->getConnection ()
        ->addColumn ($table, 'increment_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'Order Increment ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'series', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'NFE Series',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'number', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'NFE Number',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'total', array(
            'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'NFE Total',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'cfop', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 20,
            'nullable' => true,
            'comment'  => 'CFOP',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'created_at', array(
            'type' => Varien_Db_Ddl_Table::TYPE_DATE,
            'nullable' => false,
            'comment'  => 'NFE Created At',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'key_nfe', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'NFE Key',
        ));
    /*
    $now = Mage::getModel ('core/date')->gmtDate ();
    
$sqlBlock = <<< SQLBLOCK
UPDATE {$installer->getTable ($model_name)} SET created_at = '{$now}' WHERE {$conditions};
SQLBLOCK;
    
    $installer->run ($sqlBlock);
    */
}

function addOrUpdateTrackingsTable ($installer, $model_name, $comment, $conditions = null)
{
    $table = initTable ($installer, $model_name, $comment, $conditions);

    $installer->getConnection ()
        ->addColumn ($table, 'increment_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'Order Increment ID',
        ));
    $installer->getConnection ()
        ->addColumn ($table, 'code', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => false,
            'comment'  => 'Tracking Code',
        ));
    /*
    $now = Mage::getModel ('core/date')->gmtDate ();
    
$sqlBlock = <<< SQLBLOCK
UPDATE {$installer->getTable ($model_name)} SET created_at = '{$now}' WHERE {$conditions};
SQLBLOCK;
    
    $installer->run ($sqlBlock);
    */
}

function updateQuoteShippingRates ($installer)
{
    $table = $installer->getTable ('sales_flat_quote_shipping_rate');

    $installer->getConnection ()
        ->addColumn ($table, 'intelipost_quote_id', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_BIGINT,
            'length'   => 11,
            'nullable' => false,
            'unsigned' => true,
            'comment'  => 'Intelipost Quote ID',
        ));
}

addOrUpdateOrdersTable ($installer, 'intelipost_basic_orders', 'Intelipost Basic - Orders');
addOrUpdateNFEsTable ($installer, 'intelipost_basic_nfes', 'Intelipost Basic - NFEs');
addOrUpdateTrackingsTable ($installer, 'intelipost_basic_trackings', 'Intelipost Basic - Trackings');

updateQuoteShippingRates ($installer);

//demo
//Mage::getModel('core/url_rewrite')->setId(null);
//demo

$installer->endSetup();

