<?php
class Onestic_ApiServer_Model_Observer
{
    public function updateOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        //$idIntelOrder = 0;
        //if($order->getOvAutorizacao()) { // Pedido ApiClient
        //    Mage::getModel('onestic_apiserver/convidaSpecialty')->confirmOrder($order->getId());
        //}


        //if($order->getStatus()==Mage::getStoreConfig('onestic_apiserver/geral/statusped', Mage::app()->getStore())) { // Pedido ApiClient
        //    Mage::log($order->getStatus(), null, 'onestic_apiserver.log');
        //    Mage::log($order->getId(), null, 'onestic_apiserver.log');
        //    Mage::getModel('onestic_apiserver/api')->sendOrder($order->getId());
        //}


        //if(!$order->getUseragent()){
        //    $useragent = Mage::helper('core/http')->getHttpUserAgent();
        //    $order->setUseragent($useragent);
        //    $order->save();
        //}
		
        if($order->getOvOrigem()=="ROCHE") {
            /** SEND INVOICE KEY **/
            //$api = Mage::getModel('onestic_apiserver/api_orders');
            //$mpOrderId = $order->getMarketplace() . '-' . $order->getMarketplaceId(); 
            //foreach ($order->getStatusHistoryCollection() as $status) {
                //if ($status->getStatus() == "entregue") {
                    //Mage::helper('onestic_apiserver')->log('status: ' . var_export($status->getStatus(),true));

                //    $nfKey = str_replace('NF ','',$status->getComment());
                //    $api->invoice($mpOrderId,$nfKey);
                    //break;
                //}
            //}
            
            /** CHECK AND SEND SHIPMENT DATA **/
            if ($order->hasShipments() && !($order->getOvConfirmed())) {
                $items = $order->getAllVisibleItems();
                $shipment = $order->getShipmentsCollection()->getFirstItem();
                $shipmentIncrementId = $shipment->getIncrementId();
                $shippingItems = array();
                foreach ($items as $item) {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    $shippingItems[] = array(
                        "sku"   => $product->getSku(),
                        "qty"   => $item->getQtyOrdered()
                    );
                }
                
                $shipmentData = array(
                    "code"  => $shipmentIncrementId,
                    "items" => $shippingItems,
                );
                
                $trackings = $shipment->getAllTracks();
                if ($trackings) {
                    $track = $trackings[0];
                    $shipmentData['track'] = array(

                    );
                


                    $url = 'https://loja.accu-chek.com.br/apiclient/order/shipment';
                    //$url = 'https://roche.lojaemteste.com.br/index.php/apiclient/order/shipment';
                    $fields = array(
                        'token'         => urlencode('88b3caf89c7db03e5c7e0bd6e7151098'),
                        'increment_id'  => urlencode($order->getOvReferencia()),
                        'track'         => $track->getTrackNumber(),
                        'method'        => $track->getTitle() 
                    );

                    Mage::helper('onestic_apiserver')->log('Rastreio: ' . var_export($fields,true));

                    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
                    rtrim($fields_string, '&');

                    $ch = curl_init();

                    curl_setopt($ch,CURLOPT_URL, $url);
                    curl_setopt($ch,CURLOPT_POST, count($fields));
                    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

                    $result = curl_exec($ch);
                    Mage::helper('onestic_apiserver')->log('Rastreio result: ' . var_export($result,true));


                    curl_close($ch);
                    $order->setOvConfirmed("1");
                    $order->save();   
                }            


            }
        }		


        //foreach ($order->getStatusHistoryCollection() as $status) {
        //    if (strpos($status->getComment(), 'IDINTEL ') !== false) {
        //        $idIntelOrder = str_replace('IDINTEL ','',$status->getComment());
        //        break;
        //    }
        //}
        //if(($idIntelOrder)&&(!$order->getIdintelorder())){
        //    $order->setIdintelorder($idIntelOrder);
        //    $order->save();
        //}

    }
	
   public function addProductsMassaction($observer) {
    /*    $block = $observer->getEvent()->getBlock();
        $block->getMassactionBlock()->addItem('send_apiserver', array(
            'label'=> Mage::helper('onestic_apiserver')->__('Enviar para Roche'),
            'url'  => Mage::getUrl('apiserver/admin/send'),
        ));*/
    }
    
    public function addOrdersMassaction($observer) {
       /* if (!($observer->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Grid)) {
            return $this;
        }
        $block = $observer->getEvent()->getBlock();
        $block->getMassactionBlock()->addItem('send_apiserver', array(
            'label'=> Mage::helper('onestic_apiserver')->__('Sincronizar Roche'),
            'url'  => Mage::getUrl('apiserver/admin/sync'),
        ));*/
    }
    
    public function updateProduct(Varien_Event_Observer $observer)
    {/*
    	$product = $observer->getProduct();
    	Mage::getModel('onestic_apiserver/products_updater')->sync($product->getId());*/
    }  	

}
