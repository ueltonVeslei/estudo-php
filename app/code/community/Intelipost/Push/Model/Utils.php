<?php

class Intelipost_Push_Model_Utils
// extends Mage_Core_Model_Abstract
{

public function generateShipment ($order_id)
{
    $order = Mage::getModel ('sales/order')->load ($order_id);
    $increment_id = $order->getIncrementId ();
    
    if (!$order->hasInvoices())
    {
        throw new Exception('Order %d is not invoiced.', $increment_id);
        
	    //Mage::getSingleton('core/session')->addError(Mage::helper ('quote')->__('Order %d is not invoiced.', $increment_id));
	
	    return false;
    }
    
    if ($order->hasShipments ())
    {
	    foreach($order->getShipmentsCollection() as $shipment)
        {
            $track = Mage::getModel('sales/order_shipment_track')->load($shipment->getEntityId(), 'parent_id');
            
            //if (count($track->getData()) == 0)    
            //{
                $tracking_ip = Mage::getModel ('basic/trackings')->load($increment_id, 'increment_id');
                $tracking_code = $tracking_ip->getCode ();

                $tracking = array(
                    'carrier_code' => 'tracking', /* $order->getShippingMethod(), */
                    'title' => $order->getShippingDescription(),
                    'number' => $tracking_code
                );
                $track->addData($tracking);
                $shipment->addTrack($track);
                
                $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();
            //}
        }
	
	    return true;
    }
    
    $shipment = $order->prepareShipment();
    $shipment->register();
    $order->setIsInProcess(true);

    $tracking = Mage::getModel ('basic/trackings')->load($increment_id, 'increment_id');
    $tracking_code = $tracking->getCode ();

	$tracking = array(
		'carrier_code' => 'tracking', /* $order->getShippingMethod(), */
		'title' => $order->getShippingDescription(),
		'number' => $tracking_code
	);
	$track = Mage::getModel('sales/order_shipment_track')->addData($tracking);
	$shipment->addTrack($track);
	
	$shipment->sendEmail ($order->getCustomerEmail(), 'Your order has been shipped.');
	$shipment->setEmailSent (true);

    $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();
	
	$order->addStatusHistoryComment('[Intelipost] Entrega criada pela Intelipost.', false);
	$order->save ();

	return true;
}

public function orderShipped ($order_id)
{
    $order = Mage::getModel ('sales/order')->load ($order_id);
    $increment_id = $order->getIncrementId ();

    $post_data = array('order_number' => $increment_id);

    Mage::helper('basic')->setVersionControlData(Mage::helper('push')->getModuleName(), 'push');
    $intelipostApi = Mage::getModel('basic/intelipost_api');
    $intelipostApi->apiRequest(Intelipost_Basic_Model_Intelipost_Api::POST, 'shipment_order/shipped', $post_data, Mage::helper('basic')->getVersionControlModel());

    if (!$intelipostApi->_hasErrors)
    {
        $response = $intelipostApi->decodeJsonResponse(true);
        if(!strcmp($response['status'], 'OK'))
        {
            $base_order = Mage::getModel ('basic/orders')->load($order_id,'order_id');
            $base_order->setStatus('shipped');
            $base_order->save ();
        }
    }
    else
    {
    // ...
    }
}

}

