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

$table = $installer->getConnection()
	->newTable( $installer->getTable('superxmlfeed/xml') );

$table->addColumn('xml_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
		), 'XML Id')
	->addColumn('xml_filename', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
		), 'XML Filename')
	->addColumn('xml_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		), 'XML Path')
	->addColumn('xml_wrapper', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
		), 'XML Wrapper')
	->addColumn('xml_item', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable'  => false,
		), 'XML Item')
	->addColumn('xml_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable'  => true,
		), 'XML Time')
	->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'default'   => '0',
		), 'Store id');

$table->addIndex($installer->getIdxName('superxmlfeed/xml', array('store_id')),
		array('store_id'))
	->addForeignKey($installer->getFkName('superxmlfeed/xml', 'store_id', 'core/store', 'store_id'),
		'store_id', $installer->getTable('core/store'), 'store_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE,
		Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Super XML Feed');

$installer->getConnection()->createTable($table);
$installer->endSetup();
