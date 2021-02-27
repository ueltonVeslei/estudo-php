<?php

/**
 * Agence Soon
 *
 * @category    Soon
 * @package     Soon_StockReleaser
 * @copyright   Copyright (c) 2011 Agence Soon. (http://www.agence-soon.fr)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Hervé Guétin
 */
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('stockreleaser_cancel')};
CREATE TABLE {$this->getTable('stockreleaser_cancel')} (
  `id` int unsigned NOT NULL auto_increment,
  `order_id` text NOT NULL default '',
  `autocancel_date` datetime,
  `autocancel_status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
