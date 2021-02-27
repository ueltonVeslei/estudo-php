<?php
// Inicializa base do Magento
include_once('app/Mage.php');
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

// Recupera o ID do pedido desejado por GET
$orderID = $_GET['oid'];

// Inicia a captura do pedido na Skyhub
$api = Mage::getModel('onestic_skyhub/api_orders');
$skyhubOrder = $api->getOrder($orderID);
if ($skyhubOrder['httpCode'] == 200) { // Encontrou o pedido na Skyhub
	try {
		Mage::getModel('onestic_skyhub/orders')->create($skyhubOrder['body']);
		echo '<h2 style="color: green">PEDIDO ' . $orderID . ' IMPORTADO COM SUCESSO!</h2>';
	} catch (Exception $e) {
		Mage::log('ERRO POPULATE: ' . $e->getMessage(), null, 'onestic_skyhub.log');
		echo '<h2 style="color: red">ERRO AO IMPORTAR PEDIDO ' . $orderID . '</h2><p>' . $e->getMessage() . '</p>';
	}
}