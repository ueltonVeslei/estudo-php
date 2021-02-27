<?php
class Onestic_Vidalink_Model_Observer
{
    public function updateOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $idIntelOrder = 0;
        Mage::helper('onestic_vidalink')->log('Chegou no Observer - Pedido: ' . $order->getId());
        Mage::helper('onestic_vidalink')->log('Chegou no Observer - OvAutorizacao: ' . $order->getOvAutorizacao());
        if(($order->getOvAutorizacao())&&($order->getMarketplace()=="Vidalink")) { // Pedido Vidalink
        	Mage::helper('onestic_vidalink')->log('Chegou no Observer - IF OvAutorizacao: ' . $order->getId());
			Mage::getModel('onestic_vidalink/convidaSpecialty')->confirmOrder($order->getId()); 
        }

        foreach ($order->getStatusHistoryCollection() as $status) {
           if (strpos($status->getComment(), 'IDINTEL ') !== false) {
               $idIntelOrder = str_replace('IDINTEL ','',$status->getComment());
               break;
           }
       }
       if(($idIntelOrder)&&(!$order->getIdintelorder())){
       		$order->setIdintelorder($idIntelOrder);
       		$order->save();
   		} 
 
    }
       
}
