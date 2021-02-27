<?php

/**
 * Magaya
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@logistico.com so we can send you a copy immediately.
 *
 *
 * @category   Integration
 * @package    Magaya_Logistico
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->getConnection()->addColumn(
    $this->getTable('sales/order'), 
    'logistico_synced', 
    array(
            'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            'default' => false,
            'nullable' => false,
            'comment' => 'Indicates if the order was pulled by Logistico'
    )
);

$installer->getConnection()->addColumn(
    $this->getTable('sales/order_grid'), 
    'logistico_synced', 
    array(
            'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            'default' => false,
            'nullable' => false,
            'comment' => 'Indicates if the order was pulled by Logistico'
    )
);

$statusTable        = $installer->getTable('sales/order_status');
$statusStateTable   = $installer->getTable('sales/order_status_state');
$statusLabelTable   = $installer->getTable('sales/order_status_label');

$data = array(
    array('status' => 'sent_warehouse', 'label' => 'Sent to Warehouse')
);
$installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);

$data = array(
    array('status' => 'sent_warehouse', 'state' => 'processing', 'is_default' => 1)
);
$installer->getConnection()->insertArray($statusStateTable, array('status', 'state', 'is_default'), $data);

$installer->endSetup();
