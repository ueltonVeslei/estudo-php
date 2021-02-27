<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
   ->newTable($installer->getTable('onestic_overcoupom'))
   ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
       'identity'  => true,
       'unsigned'  => true,
       'nullable'  => false,
       'primary'   => true,
       ), 'Id')
   ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
       'nullable'  => true,
       ), 'Pedido')
   ->addColumn('couponcode', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
       'nullable'  => true,
       ), 'Cupom')
      ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, 255, array(
       'nullable'  => true,
       ), 'Utilizado em');
$installer->getConnection()->createTable($table);


$installer->endSetup();