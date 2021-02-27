<?php
class Onestic_Skyhub_Model_Updater extends Varien_Object {

    private $_qtyRegs = 5;

    public function orders($page=1) {
    	$total = $success = $errors = 0;
        if (Mage::helper('onestic_skyhub')->getConfig('cron_orders')) {
    	    $api = Mage::getModel('onestic_skyhub/api_queues');
    	    $per_page = Mage::helper('onestic_skyhub')->getConfig('orders_per_page');
    	    $current_page = Mage::helper('onestic_skyhub')->getConfig('current_page');
    	    if (!$current_page) $current_page = 1;
            for ($oIndex = 0; $oIndex < $per_page; $oIndex++) {
                $order = $api->getItem();
                if ($order['httpCode'] == 200) {
                    try {
                        Mage::getModel('onestic_skyhub/orders')->create($order['body']);
                        $success++;
                    } catch (Exception $e) {
                        Mage::log('ERRO QUEUE: ' . $e->getMessage(), null, 'onestic_skyhub.log');
                        $errors++;
                    }
                    $api->remove($order['body']->code);
                }
                sleep(1); // De acordo com regra de consumo da api Skyhub
            }

        }
	    return array('total' => $total,'success' => $success,'errors' => $errors);
	}
	
	public function import($page=1) {
		$count = $total = $success = $errors = $exists = 0;
		$api = Mage::getModel('onestic_skyhub/api_orders');
		$per_page = Mage::helper('onestic_skyhub')->getConfig('orders_per_page');
		$orders = $api->getCollection(array('per_page' => $per_page, 'page' => $page));
		if ($orders['httpCode'] == 200) {
			$total = $orders['body']->total;
			foreach ($orders['body']->orders as $order) {
				try {
					Mage::getModel('onestic_skyhub/orders')->create($order);
					$success++;
				} catch (Exception $e) {
					Mage::log('ERRO ALLORDERS: ' . $e->getMessage(), null, 'onestic_skyhub_import.log');
					if (strpos('EXISTE:', $e->getMessage()) !== false) {
						$exists++;
					} else {
						$errors++;
					}
				}
				$count++;
			}
			
			$this->_sendTotalEmail('PEDIDOS IMPORT', $total);
		}
		return array('total' => $total,'success' => $success,'errors' => $errors,'exists' => $exists,'count' => $count);
	}
	
	public function getTotal($conditions) {
		$api = Mage::getModel('onestic_skyhub/api_orders');
		$orders = $api->getCollection($conditions);
		$total = 0;
		if ($orders['httpCode'] == 200) {
			$total = $orders['body']->total;
		}
		
		return $total;
	}
	
	public function orderFix($order_id) {
	    $api = Mage::getModel('onestic_skyhub/api_orders');
	    $order = $api->getOrder($order_id);
	    if ($order['httpCode'] == 200) {
    	    Mage::getModel('onestic_skyhub/orders')->create($order['body']);
//    	    $orderID = Mage::getModel("sales/order")->loadByAttribute("skyhub_code", $order_id);
//    	    if ($orderID->getId()) {
//                Mage::getModel('onestic_skyhub/checker')->checkOrder($orderID->getId());
//    	    }
	    } else {
	        Mage::throwException($order['body']);
	    }	    
	}
	
	public function populate() {
	    Mage::getModel('onestic_skyhub/orders')->populate();
	}
	
	public function statusMonitoring() {

		$fromDate = date('Y-m-d H:i:s', strtotime('-4 week'));
		$toDate = date('Y-m-d H:i:s', strtotime(now()));

		$collection = Mage::getModel('onestic_skyhub/orders')->getCollection()
		    ->addFieldToFilter('status_skyhub',array(array('like' =>'Pagamento Pendente'),array('null' => true)))
            ->addFieldToFilter('status_sync',array(array('eq' =>'SYNCED')))
            ->addFieldToFilter('created_at', array('from' => $fromDate, 'to' => $toDate, 'date' => true,))
            ->addFieldToFilter('order_id',array('notnull' => true))
            ->setCurPage(1);
        $collection->getSelect()->order('updated_at ASC');
        $collection->getSelect()->limit($this->_qtyRegs);

        Mage::log($collection->getSelect()->assemble(), null, 'skyhub_status_monitoring.log');

        $api = Mage::getModel('onestic_skyhub/api_orders');
        foreach($collection as $order) {
            $checkOrder = $api->getOrder($order->getCode());
            if ($checkOrder['body']->status->label != $order->getStatusSkyhub()) {
                try {
                    Mage::getModel('onestic_skyhub/order')->updateStatus($order,$checkOrder['body']->status->label);
                    Mage::getModel('onestic_skyhub/orders')->update($order->getCode(),'status_skyhub',$checkOrder['body']->status->label);
                } catch (Exception $e) {
                    Mage::log('ERRO statusMonitoring (' . $order->getCode() . '): ' . $e->getMessage(),null,'onestic_skyhub.log');
                    Mage::log('ERRO statusMonitoring (' . $order->getCode() . '): ' . $e->getMessage(),null,'skyhub_status_monitoring.log');
                }

            }
            Mage::getModel('onestic_skyhub/orders')->update($order->getCode(),'updated_at',date('Y-m-d H:i:s'));
        }
	}
	
	public function syncExported() {
		$collection = Mage::getModel('onestic_skyhub/orders')->getCollection()
			->addFieldToFilter('status_sync',array('like' => 'NOT_SYNCED'))
			->addFieldToFilter('order_id',array('notnull' => true))
			->setOrder('updated_at', 'DESC');
		
		$total = $collection->getSize();
		
		$collection->setPageSize($this->_qtyRegs)
			->setCurPage(1)
			->setOrder('id','DESC');
		foreach($collection as $order) {
			Mage::getModel('onestic_skyhub/checker')->export($order->getOrderId());
			Mage::getModel('onestic_skyhub/orders')->update($order->getCode(),'updated_at',date('Y-m-d H:i:s'));
		}
		
		//$this->_sendTotalEmail('Exported', $total);
	}
	
	public function syncInvoice() {
		$per_page = Mage::helper('onestic_skyhub')->getConfig('orders_per_page');
		$collection = Mage::getModel('onestic_skyhub/orders')->getCollection()
			->addFieldToFilter('status_invoice_mg',array('like' => 'SIM'))
			->addFieldToFilter('status_invoice_sh',array('like' => 'NÃO'))
			->addFieldToFilter('status_skyhub',array('nin' => array('Cancelado','pedido enviado','completo (entregue)')))
			->addFieldToFilter('order_id',array('notnull' => true))
			->setOrder('updated_at', 'DESC');
		
		$total = $collection->getSize();
		
		$collection->setPageSize($per_page)
			->setCurPage(1);
		
		$success = $errors = $count = 0;
		foreach($collection as $order) {
			try {
				Mage::getModel('onestic_skyhub/checker')->invoice($order->getOrderId());
				$success++;
			} catch (Exception $e) {
				$errors++;
			}
			$count++;
			Mage::getModel('onestic_skyhub/orders')->update($order->getCode(),'updated_at',date('Y-m-d H:i:s'));
		}
		
		//$this->_sendTotalEmail('Invoice', $total);
		return array('total' => $total,'success' => $success,'errors' => $errors, 'count' => $count);
	}
	
	public function syncShipment() {
		$per_page = Mage::helper('onestic_skyhub')->getConfig('orders_per_page');
		$collection = Mage::getModel('onestic_skyhub/orders')->getCollection()
			->addFieldToFilter('status_shipment_mg',array('like' => 'SIM'))
			->addFieldToFilter('status_shipment_sh',array('like' => 'NÃO'))
			->addFieldToFilter('order_id',array('notnull' => true))
			->setOrder('updated_at', 'DESC');
		
		$total = $collection->getSize();
		
		$collection->setPageSize($per_page)
			->setCurPage(1);
		
		$success = $errors = $count = 0;
		foreach($collection as $order) {
			try {
				Mage::getModel('onestic_skyhub/checker')->shipment($order->getOrderId());
				$success++;
			} catch (Exception $e) {
				$errors++;
			}
			$count++;
			Mage::getModel('onestic_skyhub/orders')->update($order->getCode(),'updated_at',date('Y-m-d H:i:s'));
		}
		
		//$this->_sendTotalEmail('Shipment', $total);
		return array('total' => $total,'success' => $success,'errors' => $errors, 'count' => $count);
	}
	
	public function syncDelivery() {
		$per_page = Mage::helper('onestic_skyhub')->getConfig('orders_per_page');
		$collection = Mage::getModel('onestic_skyhub/orders')->getCollection()
			->addFieldToFilter('status_delivery_mg',array('like' => 'SIM'))
			->addFieldToFilter('status_delivery_sh',array('like' => 'NÃO'))
			->addFieldToFilter('order_id',array('notnull' => true))
			->setOrder('updated_at', 'DESC');
		
		$total = $collection->getSize();
		
		$collection->setPageSize($per_page)
			->setCurPage(1);
		
		$success = $errors = $count = 0;
		foreach($collection as $order) {
			try {
				Mage::getModel('onestic_skyhub/checker')->delivery($order->getOrderId());
				$success++;
			} catch (Exception $e) {
				$errors++;
			}
			$count++;
			Mage::getModel('onestic_skyhub/orders')->update($order->getCode(),'updated_at',date('Y-m-d H:i:s'));
		}
		
		//$this->_sendTotalEmail('Delivery', $total);
		return array('total' => $total,'success' => $success,'errors' => $errors, 'count' => $count);
	}
	
	protected function _sendTotalEmail($text, $total) {
		if ($total > $this->_qtyRegs) {
			$senderName = Mage::getStoreConfig('trans_email/ident_general/name');
			$senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');
				
			$mail = new Zend_Mail();
			$mail->setFrom($senderEmail, $senderName);
			$mail->addTo('anderson@onestic.com', 'Anderson Vincoletto');
			$mail->setSubject('Total fila de Sync: ' . $text);
			$mail->setBodyHtml("Verificar total de registros da fila " . $text . ".<br />Total de registros na fila: " . $total);
			$mail->send();
		}
	}
	
}