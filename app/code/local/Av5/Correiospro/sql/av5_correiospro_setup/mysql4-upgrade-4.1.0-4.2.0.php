<?php
$installer = $this;
$installer->startSetup();

$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('tabela_correiospro')};
		CREATE TABLE {$this->getTable('tabela_correiospro')} (
		`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
		`website_id` INTEGER NOT NULL,
		`store_id` INTEGER NOT NULL,
		`source` VARCHAR(8) NOT NULL,
		`region` INTEGER,
		`prices` TEXT,
		`declared` TEXT,
		`risk` TEXT,
		`updated_at` DATETIME,
		PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('ceps_correiospro')};
		CREATE TABLE {$this->getTable('ceps_correiospro')} (
		`id`  INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
		`city` varchar(255) NOT NULL,
		`state` varchar(2) NOT NULL,
		`neighbor` varchar(255) NOT NULL,
		`start` varchar(8) NOT NULL,
		`end` varchar(8) NOT NULL,
		PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");


$installer->endSetup();
