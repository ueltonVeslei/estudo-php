<?php
require_once ("app/Mage.php");
ini_set('display_errors', 1);
umask(0);
Mage::app();

/* SCRIPT PARA SETAR ENDEREÇO DE COBRANÇA E ENTREGA COMO PADRÃO QUANDO NÃO HOUVER SETADO */

$collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*');

foreach ($collection as $customer) {
	
	$customerObj = Mage::getModel('customer/customer')->load( $customer->getId() );
	
	if ( ! $customerObj->getDefaultBillingAddress() ) {
		foreach ($customerObj->getAddresses() as $address) {
			$address->setIsDefaultBilling('1');
			$address->save();
			continue;
		}
	}

	if ( ! $customerObj->getDefaultShippingAddress() ) {
		foreach ($customerObj->getAddresses() as $address) {
			$address->setIsDefaultShipping('1');
			$address->save();
			continue;
		}
	}
}