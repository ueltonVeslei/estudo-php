<?php

class Intelipost_Push_WebhookController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
	
        if ($this->getRequest()->isPost())
		{
			if ($this->getRequest()->getHeader('api-key') == Mage::helper('basic')->getDecriptedKey('apikey'))
			{
				$value = json_decode($this->getRequest()->getRawBody());
				$comment = 'Intelipost Notificação Pedido:' . ' ' . $value->history->shipment_order_volume_state_localized;
				$order_id = $value->order_number;

				$order = Mage::getModel('sales/order')->load($order_id, 'increment_id');
				$order->addStatusHistoryComment($comment);
				$order->save();				
			}
		}      
	}
}
