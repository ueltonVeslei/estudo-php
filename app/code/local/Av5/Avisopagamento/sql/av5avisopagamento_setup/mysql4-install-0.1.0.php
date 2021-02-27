<?php
$installer = $this;
$installer->startSetup();

$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('av5_pagwarn_log')};
		CREATE TABLE {$this->getTable('av5_pagwarn_log')} (
		`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
		`order` INTEGER,
		`date` DATETIME,
		PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();