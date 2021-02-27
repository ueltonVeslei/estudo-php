<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
	->addColumn($installer->getTable('basic/orders'),
    	'shipping_cost', "decimal(12,4) NOT NULL default '0.0000'");    

$installer->endSetup();