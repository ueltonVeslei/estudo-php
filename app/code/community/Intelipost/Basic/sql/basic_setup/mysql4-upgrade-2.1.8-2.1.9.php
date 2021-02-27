<?php

$installer = $this;
$installer->startSetup ();

$table = $installer->getTable ('sales_flat_quote_shipping_rate');

    $installer->getConnection ()
        ->addColumn ($table, 'intelipost_restricted_msg', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'nullable' => true,
            'comment'  => 'Restricted Area Message',
        ));
   $installer->endSetup();