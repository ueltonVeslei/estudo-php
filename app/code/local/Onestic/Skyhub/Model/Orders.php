<?php
class Onestic_Skyhub_Model_Orders extends Mage_Core_Model_Abstract {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('onestic_skyhub/orders');
	}
	
	public function populate() {
	    $api = Mage::getModel('onestic_skyhub/api_orders');
	    $per_page = Mage::helper('onestic_skyhub')->getConfig('orders_per_page');
	    $current_page = Mage::helper('onestic_skyhub')->getConfig('current_populate_page');
	    if (!$current_page) $current_page = 1;
	    $orders = $api->getCollection(array('per_page' => $per_page, 'page' => $current_page, 'filters[sync_status][]' => ''));
	    $count = $success = $errors = 0;
	    foreach ($orders['body']->orders as $order) {
	        try {
				$this->create($order);
				$success++;
	        } catch (Exception $e) {
	            Mage::log('ERRO POPULATE: ' . $e->getMessage(), null, 'onestic_skyhub.log');
	            $errors++;
	        }
	        $count++;
	    }
	    
	    if ($count == $per_page) { // ATUALIZA NÚMERO DA PÁGINA DE REGISTRO DOS PEDIDOS
    	    Mage::helper('onestic_skyhub')->updateConfig('current_populate_page',$current_page+1);
	    }
	    
	    return array('total' => $orders['body']->total,'success' => $success,'errors' => $errors);
	}
	
	public function create($order) {

		//Validação amazon
		if($order->shipping_address->region == null || $order->shipping_address->region == '' || $order->shipping_address->region == 'N/A') {
			Mage::log('Atribute shipping_address->region esta com valor nulo ou vazio, não foi possível importar Order!', 'onestic_skyhub.log');
			return false;
		}
		
		if($order->billing_address->region == null || $order->billing_address->region == '' || $order->billing_address->region == 'N/A') {
			Mage::log('Atribute billing_address->region esta com valor nulo ou vazio, não foi possível importar Order!', 'onestic_skyhub.log');
			return false;
		}
		//=============================================================

		$orderExists = Mage::getModel('onestic_skyhub/orders')->load($order->code, 'code');
		if (!$orderExists->getId()) {
		    $data = array(
		        'code'             => $order->code,
		        'created_at'       => str_replace('T',' ',$order->placed_at),
		        'name'             => $order->customer->name,
		        'status_skyhub'    => $order->status->label,
		        'status_sync'      => $order->sync_status
		    );
		    $hasOrder = false;
		    $orderMage = Mage::getModel("sales/order")->loadByAttribute("skyhub_code", $order->code);
		    if ($orderMage->getId()) {
		        $data['order_id'] = $orderMage->getId();
		        $data['increment_id'] = $orderMage->getIncrementId();
		        $hasOrder = true;
		    } else {
		    	$exported = Mage::getModel('onestic_skyhub/order')->create($order);
		    	if ($exported) {
		    		$orderMage = Mage::getModel("sales/order")->loadByAttribute("skyhub_code", $order->code);
		    		$data['order_id'] = $orderMage->getId();
		    		$data['increment_id'] = $orderMage->getIncrementId();
		    		$hasOrder = true;
		    	}
		    }
		    $this->setData($data);
		    try {
		       $this->save();
		    } catch (Exception $e) {
		        Mage::log('ERRO POPULATE: ' . $e->getMessage(), null, 'onestic_skyhub_create.log');
		        Mage::throwException('CREATE: ' . $e->getMessage());
		    }
		    
		    if ($hasOrder) {
		        Mage::getModel('onestic_skyhub/checker')->checkOrder($orderMage->getId());
		    }
		} else {
            $orderMage = Mage::getModel("sales/order")->loadByAttribute("skyhub_code", $order->code);
            if ($orderMage->getId()) {
                Mage::getModel('onestic_skyhub/checker')->checkOrder($orderMage->getId());
            } else {
                Mage::throwException('EXISTE: Pedido já incluído = ' . $orderExists->getId() . ' = ' . $orderExists->getCode());
            }
		}
	}
	
	public function update($code,$status,$value) {
	    $order = $this->load($code, 'code');
	    if ($order->getId()) {
	    	$order->setData($status,$value);
	    	$order->save();
	    }
	}

}