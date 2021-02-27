<?php
$installer = $this;
$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('reviewplus_clientlog')};
CREATE TABLE IF NOT EXISTS {$this->getTable('reviewplus_clientlog')} (
`id` int(12) unsigned NOT NULL auto_increment,
`enable` tinyint(1) NOT NULL ,
`order_id` int(12) NOT NULL ,
`customer_name` varchar(255) NOT NULL ,
`customer_email` varchar(255) NOT NULL ,
`ordered_products` varchar(255) NOT NULL ,
`send_time` int(16) NOT NULL ,
`status` tinyint(1) NOT NULL ,
PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('reviewplus_reviews')};
CREATE TABLE IF NOT EXISTS {$this->getTable('reviewplus_reviews')} (
`id` int(12) unsigned NOT NULL auto_increment,
`order_id` int(12) NOT NULL ,
`product_sku` varchar(255) NOT NULL ,
`customer_name` varchar(255) NOT NULL ,
`customer_email` varchar(255) NULL ,
`product_rating` tinyint(1) NOT NULL ,
`product_review` text NOT NULL ,
`review_status` varchar(100) NOT NULL ,
`store_id` int(3) NOT NULL ,
PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
?>