<?php

//load the magento app with the admin store
require_once '../../../../Mage.php';

Mage::app();

echo "Começando<br />";

// Get OrderID that we are to change the status on.  I get mine from a Form POST
$orderId = isset($_GET['orderid']) ? $_GET['orderid'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

if(isset($orderId) && $orderId != ''){
  echo 'Atualizando status do pedido: '. $orderId;

  $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

  if ($status == 'processing') {
  	$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
    echo '<br />';
    echo 'Pedido '. $orderId . ' atualizado para processando.';
  } else if ($status == 'holded') {
  	$order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true)->save();
    echo '<br />';
    echo 'Pedido '. $orderId . ' atualizado para segurado.';
  }
}