<?php
class Onestic_ApiServer_Model_Orders extends Mage_Core_Model_Abstract {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('onestic_apiserver/orders');
	}
	
	public function create($order) {
		$orderExists = Mage::getModel('onestic_apiserver/orders')->load($order->IdPedido, 'code');
		if (!$orderExists->getId()) {
		    $data = array(
		        'code'             => $order->IdPedido,
		        'created_at'       => str_replace('T',' ',$order->Data),
		        'name'             => $order->Cliente->Nome,
		        'status_sync'      => 1,
                'order_data'       => json_encode($order),
		    );

		    $orderMage = Mage::getModel("sales/order")->loadByAttribute("marketplace_id", $order->IdPedido);
		    if ($orderMage->getId()) {
		        $data['order_id'] = $orderMage->getId();
		        $data['increment_id'] = $orderMage->getIncrementId();
		    } else {
		        try {
                    Mage::getModel('onestic_apiserver/order')->create($order);
                    $orderMage = Mage::getModel("sales/order")->loadByAttribute("marketplace_id", $order->IdPedido);
                    $data['order_id'] = $orderMage->getId();
                    $data['increment_id'] = $orderMage->getIncrementId();
                } catch(Exception $e) {
                    Mage::log('ERRO CREATE: ' . $e->getMessage(), null, 'onestic_apiserver_create.log');
                }
		    }

		    $this->setData($data);

		    try {
		       $this->save();
		    } catch (Exception $e) {
		        Mage::log('ERRO POPULATE: ' . $e->getMessage(), null, 'onestic_apiserver_create.log');
		        Mage::throwException('CREATE: ' . $e->getMessage());
		    }
		} else {
			Mage::throwException('EXISTE: Pedido já incluído = ' . $orderExists->getId() . ' = ' . $orderExists->getCode());
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