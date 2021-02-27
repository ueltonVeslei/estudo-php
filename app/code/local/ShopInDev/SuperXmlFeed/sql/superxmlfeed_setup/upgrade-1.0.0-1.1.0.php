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
	'conditions_serialized',
	array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'nullable' => true,
		'default' => null,
		'comment'  => 'Conditions Serialized'
	)
);

$installer->endSetup();
