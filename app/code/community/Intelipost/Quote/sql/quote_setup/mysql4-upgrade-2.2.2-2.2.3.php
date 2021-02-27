<?php

$installer = $this;
$installer->startSetup ();

$table = $installer->getTable ('sales_flat_quote_shipping_rate');

    $installer->getConnection ()
        ->addColumn ($table, 'intelipost_estimated_delivery_business_days', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 11,
            'nullable' => false,
            'unsigned' => true,
            'comment'  => 'Intelipost Estimated Delivery Business Days'
        	));

   $installer->endSetup();