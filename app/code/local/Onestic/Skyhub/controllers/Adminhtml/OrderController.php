<?php
class Onestic_Skyhub_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		$this->loadLayout()->renderLayout();
	}
	
	public function syncAction() {
	    $controlId = $this->getRequest()->getParam('id');
	    $order = Mage::getModel('onestic_skyhub/orders')->load($controlId);
	    if ($order->getOrderId()) {
		    $result = Mage::getModel('onestic_skyhub/checker')->checkOrder($order->getOrderId());
	        $orderData = Mage::getModel('sales/order')->load($order->getOrderId());
	        
		    if ($result) {
		        Mage::getSingleton('adminhtml/session')->addSuccess("Pedido " . $orderData->getIncrementId() . " sincronizado com Skyhub!");
		    } else {
		        Mage::getSingleton('adminhtml/session')->addError("Pedido " . $orderData->getIncrementId() . " não sincronizado com Skyhub!");
		    }
	    } else {
	    	if (!$order->getCode()) {
	    		$order->delete();
	    		Mage::getSingleton('adminhtml/session')->addError("Registro excluído por não ter vínculo com pedido Skyhub!");
	    	} else { // Tenta vincular o pedido a um pedido Magento
	    		$api = Mage::getModel('onestic_skyhub/api_orders');
	    		$apiOrder = $api->getOrder($order->getCode());
	    		if ($apiOrder['httpCode'] == 200) {
	    			Mage::getModel('onestic_skyhub/order')->create($apiOrder['body']);
	    			$mOrder = Mage::getModel("sales/order")->loadByAttribute("skyhub_code", $order->getCode());
	    			if ($mOrder->getId()) {
	    				$order->setData('order_id',$mOrder->getId());
	    				$order->setData('increment_id',$mOrder->getIncrementId());
	    				$order->save();
	    				$result = Mage::getModel('onestic_skyhub/checker')->checkOrder($mOrder->getId());
	    				if ($result) {
	    					Mage::getSingleton('adminhtml/session')->addSuccess("Pedido " . $order->getIncrementId() . " sincronizado com Skyhub!");
	    				} else {
	    					Mage::getSingleton('adminhtml/session')->addError("Pedido " . $order->getIncrementId() . " não sincronizado com Skyhub!");
	    				}
	    			} else {
	    				Mage::getSingleton('adminhtml/session')->addError("Não foi possível criar o pedido ".$order->getCode()." no Magento, verifique o log de erros para mais informações!");
	    			}
	    		} else {
	    			Mage::getSingleton('adminhtml/session')->addError("Não foi possível recuperar as informações do pedido " . $order->getCode() . " na Skyhub!");
	    		}
	    	}
	    }
	    $this->_redirect('*/*/');
	}
	
	public function lastprogressAction() {
	    $result = Mage::getModel('onestic_skyhub/updater')->orders();	    
	    echo $result['success'] . "|" . $result['errors'] . "|" . $result['total'];
	}
	
	public function lastAction() {
		$this->loadLayout()->renderLayout();
	}
	
	public function importprogressAction() {
		if(!Mage::getModel('core/session')->getImportPage()) {
			Mage::getModel('core/session')->setImportPage(1);
		}
		if(!Mage::getModel('core/session')->getImportTotal()) {
			Mage::getModel('core/session')->setImportTotal(0);
		}
		$total = Mage::getModel('onestic_skyhub/updater')->getTotal(array('per_page'=>1,'page'=>1));
		$page = Mage::getModel('core/session')->getImportPage();
		$syncTotal = Mage::getModel('core/session')->getImportTotal();
		$success = $errors = 0;
		if ($syncTotal < $total) {
			$result = Mage::getModel('onestic_skyhub/updater')->import($page);
			$syncTotal += $result['count'];
			$success = $result['success'];
			$errors = $result['errors'];
			$exists = $result['exists'];
			Mage::getModel('core/session')->setImportTotal($syncTotal);
			Mage::getModel('core/session')->setImportPage($page+1);
		} else {
			$syncTotal = 0;
		}
		echo json_encode(array('count'=>$syncTotal,'success'=>$success,'exists'=>$exists,'errors'=>$errors,'total'=>$total));
	}
	
	public function importAction() {
		Mage::getModel('core/session')->setImportPage(1);
		Mage::getModel('core/session')->setImportTotal(0);
		$this->loadLayout()->renderLayout();
	}
	
	public function invoiceAction() {
		Mage::getModel('core/session')->setInvoicePage(1);
		Mage::getModel('core/session')->setInvoiceTotal(0);
		$this->loadLayout()->renderLayout();
	}
	
	public function shipmentAction() {
		Mage::getModel('core/session')->setShipmentPage(1);
		Mage::getModel('core/session')->setShipmentTotal(0);
		$this->loadLayout()->renderLayout();
	}
	
	public function deliveryAction() {
		Mage::getModel('core/session')->setDeliveryPage(1);
		Mage::getModel('core/session')->setDeliveryTotal(0);
		$this->loadLayout()->renderLayout();
	}
	
	public function resyncprogressAction() {
		Mage::getModel('core/session')->setSyncPage(1);
		Mage::getModel('core/session')->setSyncTotal(0);
		$this->loadLayout()->renderLayout();
	}
	
	public function resyncAction() {
		if(!Mage::getModel('core/session')->getSyncPage()) {
			Mage::getModel('core/session')->setSyncPage(1);
		}
		if(!Mage::getModel('core/session')->getSyncTotal()) {
			Mage::getModel('core/session')->setSyncTotal(0);
		}
		$page = Mage::getModel('core/session')->getSyncPage();
		$syncTotal = Mage::getModel('core/session')->getSyncTotal();
		$total = Mage::getModel('onestic_skyhub/orders')->getCollection()->getSize();
		$success = $errors = 0;
		if ($syncTotal < $total) {
			$orders = Mage::getModel('onestic_skyhub/orders')
							->getCollection()
							->addFieldToFilter('status_skyhub',array('nin',array('Cancelado','completo (entregue)')))
							->setPageSize(100)
							->setCurPage($page);
			$checker = Mage::getModel('onestic_skyhub/checker');
			foreach($orders as $order) {
				if ($order->getOrderId()) {
					try {
						/* VERIFICAÇÃO DE PEDIDOS */
						$result = $checker->checkOrder($order->getOrderId());
						if ($result) {
							$success++;
						} else {
							$errors++;
						}
					} catch(Exception $e) {
						$errors++;
					}
				} else {
					$errors++;
				}
				$syncTotal++;
			}
			Mage::getModel('core/session')->setSyncTotal($syncTotal);
			Mage::getModel('core/session')->setSyncPage($page+1);
		} else {
			$syncTotal = 0;
		}
		echo json_encode(array('count'=>$syncTotal,'success'=>$success,'errors'=>$errors,'total'=>$total));
	}
	
	public function deleteAction() {
		$controlId = $this->getRequest()->getParam('id');
		if ($controlId) {
			$order = Mage::getModel('onestic_skyhub/orders')->load($controlId);
			if ($order->getId()) {
				$codeOrder = $order->getCode();
				$order->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess("Pedido " . $codeOrder . " excluído com sucesso!");
			} else {
				Mage::getSingleton('adminhtml/session')->addSuccess("Pedido não encontrado!");
			}
		} else {
			Mage::getSingleton('adminhtml/session')->addSuccess("Código do pedido não informado!");
		}
		$this->_redirect('*/*/');
	}
	
	public function _isAllowed() {
	    return true;
	}
}