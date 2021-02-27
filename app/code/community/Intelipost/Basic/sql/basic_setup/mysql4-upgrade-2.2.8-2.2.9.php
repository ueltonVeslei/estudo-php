<?php

$installer = $this;
$installer->startSetup ();

$table = $installer->getTable ('intelipost_basic_orders');

    $installer->getConnection ()
        ->addColumn ($table, 'qty_volumes', array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'unsigned' => true,
            'nullable' => false,
            'comment'  => 'Quantity Volumes',
        ));
   $installer->endSetup();