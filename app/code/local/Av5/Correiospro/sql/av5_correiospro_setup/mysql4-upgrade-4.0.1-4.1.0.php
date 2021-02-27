<?php
$installer = $this;
$installer->startSetup();

$status = Mage::getModel('sales/order_status');
$status->setStatus(Av5_Correiospro_Model_Tracking::ORDER_SHIPPED_STATUS)
	->setLabel('Pedido em Transporte')
	->assignState(Mage_Sales_Model_Order::STATE_COMPLETE)
	->save();

$status->setStatus(Av5_Correiospro_Model_Tracking::ORDER_DELIVERED_STATUS)
    ->setLabel('Pedido Entregue')
    ->assignState(Mage_Sales_Model_Order::STATE_COMPLETE)
    ->save();

$status = Mage::getModel('sales/order_status');
$status->setStatus(Av5_Correiospro_Model_Tracking::ORDER_WARNED_STATUS)
    ->setLabel('Dificuldade na Entrega')
    ->assignState(Mage_Sales_Model_Order::STATE_COMPLETE)
    ->save();

$installer->endSetup();
