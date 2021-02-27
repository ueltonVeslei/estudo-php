<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
->addColumn($installer->getTable('basic/orders'),
    	'delivery_business_day', "int(10) unsigned NULL default '0'");

$installer->endSetup();