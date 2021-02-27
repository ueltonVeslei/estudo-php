<?php

class Intelipost_Push_Block_Tracking_Popup extends Mage_Shipping_Block_Tracking_Popup
{
	protected $clientId;
	protected $info;
	public function __construct()
    {
        $this->info = Mage::registry('current_shipping_info');         
        $track = Mage::getModel('sales/order_shipment_track')->load($this->info->getOrderId(), 'order_id');
        if ($track->getCarrierCode() == 'intelipost') {
            $this->setTemplate('intelipost/tracking/popup.phtml');
        }
        else {
        	$this->setTemplate('shipping/tracking/popup.phtml');
        }
        //Mage::log($track->getData());
    }

    public function getClientId()
    {
    	if (!$this->clientId)
    	{
    		$methodsInfo = Mage::helper('basic')->getMethodsInfo('push');
    		$this->clientId = $methodsInfo->content->id;
    	}

    	return $this->clientId;
    }

    public function getIncrementId()
    {
    	$order = Mage::getModel('sales/order')->load($this->info->getOrderId());
    	return $order->getIncrementId();
    }
}