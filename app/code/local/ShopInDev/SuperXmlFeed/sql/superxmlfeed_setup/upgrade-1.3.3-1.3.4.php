<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
	$installer->getTable('superxmlfeed/xml'),
	'include_invisible',
	array(
		'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'nullable' => true,
		'default' => 0,
		'comment'  => 'Include Invisible Products'
	)
);

$installer->endSetup();
