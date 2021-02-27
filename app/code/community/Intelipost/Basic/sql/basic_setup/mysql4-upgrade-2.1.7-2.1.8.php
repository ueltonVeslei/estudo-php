<?php

$installer = $this;
$installer->startSetup ();

$table = $installer->getTable ('sales_flat_quote_shipping_rate');

    $installer->getConnection ()
        ->addColumn ($table, 'intelipost_cost', array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => false,
            'scale'     => 2,
        	'precision' => 12,
            'comment'  => 'Shipping Cost',
        ));
   $installer->endSetup();