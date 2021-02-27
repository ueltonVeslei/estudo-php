<?php

class Intelipost_Basic_Model_Request_Shipment_Ready_Order
// extends Varien_Object
{
	public $order_number;

	public function fetchReadyShipmentOrderRequest($order_id)
    {
    	$this->order_number = $order_id;
    }
}