<?php
class Onestic_Skyhub_Model_Checker {

    // SUCCESS
    CONST STATUS_TO_EXPORT      = 0;
    CONST STATUS_TO_INVOICE     = 1;
    CONST STATUS_TO_SHIP        = 2;
    CONST STATUS_TO_DELIVER     = 3;
    CONST STATUS_COMPLETE       = 100;
    
    // ERRORS
    CONST STATUS_EXPORT_ERROR   = 10;
    CONST STATUS_INVOICE_ERROR  = 20;
    CONST STATUS_SHIPMENT_ERROR = 30;
    CONST STATUS_DELIVERY_ERROR = 40;
    CONST STATUS_NO_RESOURCE    = 99;
    
    private $_order             = NULL; // PEDIDO MAGENTO
    private $_apiOrder          = NULL; // PEDIDO SKYHUB
    private $_control           = NULL; // REGISTRO DO CONTROLE
    private $_api               = NULL; // MODEL API
	private $_apiOff			= TRUE; // FLAG DE DISPONIBILIDADE DA API
	
	private $_hasInvoice		= FALSE; // FLAG DE CONTROLE DE INVOICE EM PEDIDO SKYHUB
	private $_hasShipment		= FALSE; // FLAG DE CONTROLE DE SHIPMENT EM PEDIDO SKYHUB
	private $_hasDelivered		= FALSE; // FLAG DE CONTROLE DE DELIVERED EM PEDIDO SKYHUB
	private $_hasOrder			= FALSE; // FLAG DE CONTROLE DE CARREGAMENTO DO PEDIDO
	private $_hasControl		= FALSE; // FLAG DE CONTROLE DE CARREGAMENTO DO PEDIDO DE CONTROLE
    
    private $_errors            = 0; // QUANTIDADE DE ERROS
    
    protected function _init($orderId, $onlyLocal=FALSE) {
    	$this->_hasInvoice = FALSE;
    	$this->_hasShipment = FALSE;
    	$this->_hasDelivered = FALSE;
    	$this->_hasOrder = FALSE;
    	$this->_hasControl = FALSE;
    	$this->_apiOff = TRUE;
    	$this->_order = Mage::getModel('sales/order')->load($orderId);
    	if ($this->_order->getId()) {
    		$this->_hasOrder = TRUE;
    		$this->_control = Mage::getModel('onestic_skyhub/orders')->load($this->_order->getSkyhubCode(), 'code');
    		if ($this->_control->getId()) {
    			$this->_hasControl = TRUE;
	    		if(!$onlyLocal) {
	    			$this->_api = Mage::getModel('onestic_skyhub/api_orders');
	    			$this->_apiOrder = $this->_api->getOrder($this->_order->getSkyhubCode());
	    			if ($this->_apiOrder['httpCode'] == 200) { // ENCONTROU PEDIDO NA SKYHUB
	    				Mage::log('CARREGOU API ORDER: ' . $this->_apiOrder['body']->code,null,'checker.log');
	    				$this->_apiOff = FALSE;
	    			}
	    		}
    		}
    	}
    }
    
    public function checkOrder($orderId, $onlyLocal=FALSE) {
    	$this->_init($orderId, $onlyLocal);
    	
    	if (!$this->_hasOrder || !$this->_hasControl) {
    		return false;
    	}
    	
    	if(!$this->_apiOff) {
			Mage::log('CARREGOU API ORDER: ' . $this->_apiOrder['body']->code,null,'checker.log');
			$this->_apiOff = FALSE;
                
			if ($this->_apiOrder['body']->status->label != $this->_control->getStatusSkyhub()) {
				Mage::getModel('onestic_skyhub/orders')->update($this->_control->getCode(),'status_skyhub',$this->_apiOrder['body']->status->label);
				Mage::getModel('onestic_skyhub/order')->updateStatus($this->_control,$this->_apiOrder['body']->status->label);
			}
				
            if ($this->_apiOrder['body']->sync_status != 'SYNCED') { // SINCRONIZA O PEDIDO NA SKYHUB
            	$this->export();
			} else {
            	if ($this->_control->getStatusSync() != $this->_apiOrder['body']->sync_status) { // ATUALIZA STATUS LOCAL
                	Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_sync',$this->_apiOrder['body']->sync_status);
				}
			}
                
            if ($this->_apiOrder['body']->invoices) {
            	$this->_hasInvoice = TRUE;
                if ($this->_control->getStatusInvoiceSh() == 'NÃO') { // ATUALIZA STATUS LOCAL
                	Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_invoice_sh','SIM');
				}
			}
                
            if ($this->_apiOrder['body']->shipments) {
            	$this->_hasShipment = TRUE;
                if ($this->_control->getStatusShipmentSh() == 'NÃO') { // ATUALIZA STATUS LOCAL
                	Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_shipment_sh','SIM');
                }
			}
                
            if ($this->_apiOrder['body']->status->type == 'DELIVERED') {
            	$this->_hasDelivered = TRUE;
                if ($this->_control->getStatusDeliverySh() == 'NÃO') { // ATUALIZA STATUS LOCAL
                	Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_delivery_sh','SIM');
				}
			}
        }
        
        $this->_checkStatus();        
		$this->invoice();
		$this->shipment();
		$this->delivery();
        
        return true;
    }
    
    public function export($orderId=NULL, $onlyLocal=FALSE) {
        if (Mage::helper('onestic_skyhub')->getConfig('exported')) {
        	if ($orderId) $this->_init($orderId, $onlyLocal);
        	if($this->_hasOrder && !$this->_apiOff) {
				Mage::log('PEDIDO '.$this->_order->getSkyhubCode().' EXPORTADO COM SUCESSO',null,'onestic_skyhub.log');
				Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_sync','SYNCED');
				
	            /*$result = $this->_api->exported($this->_order->getSkyhubCode());
	            $message = $this->_api->checkResponseErrors($result['httpCode']);
	            if ($message) {
	                Mage::log('EXPORTED ERROR '.$this->_order->getSkyhubCode().': ' . $message,null,'onestic_skyhub.log');
	                $this->_errors++;
	            } else {
	                Mage::log('PEDIDO '.$this->_order->getSkyhubCode().' EXPORTADO COM SUCESSO',null,'onestic_skyhub.log');
	                Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_sync','SYNCED');
	            }*/
        	}
        }
    }
    
    public function invoice($orderId=NULL, $onlyLocal=FALSE) {
    	if ($orderId) $this->_init($orderId, $onlyLocal);
    	if($this->_hasOrder && !$this->_apiOff) {
	        if ($this->_control->getStatusInvoiceSh() == 'NÃO' || $this->_control->getStatusInvoiceMg() == 'NÃO') {
	            $nfKey = null;
	            foreach ($this->_order->getStatusHistoryCollection() as $status) {
	                if (strpos($status->getComment(), 'NF ') !== false) {
	                    $nfKey = str_replace('NF ','',$status->getComment());
	                    break;
	                }
	            }
	
	            if ($nfKey) {
					Mage::log('TEM NF: ' . $this->_order->getIncrementId() . '-' . $this->_order->getSkyhubCode(),null,'checker.log');
					Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_invoice_mg','SIM');
					
	                
					if (!$this->_apiOff && !$this->_hasInvoice) {
						$result = $this->_api->invoice($this->_order->getSkyhubCode(),trim($nfKey));
						$message = $this->_api->checkResponseErrors($result['httpCode']);
						Mage::log('TEM NF: ' . $this->_order->getIncrementId() . '-' . $this->_order->getSkyhubCode() . " - HttpCode: " . $result['httpCode'], null,'checker.log');

						
						if ($result['httpCode']!=201) {
							Mage::log('INVOICE ERROR '.$this->_order->getSkyhubCode().': ' . $message,null,'onestic_skyhub.log');
							$this->_errors++;
						} else {
							Mage::log('NF DO PEDIDO '.$this->_order->getSkyhubCode().' ENVIADA COM SUCESSO',null,'onestic_skyhub.log');
							Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_invoice_sh','SIM');
						}
					}
	            }
	        }
    	}
    }
    
    public function shipment($orderId=NULL, $onlyLocal=FALSE) {
    	if ($orderId) $this->_init($orderId, $onlyLocal);
    	if($this->_hasOrder && !$this->_apiOff) {
	        if ($this->_order->hasShipments() && ($this->_control->getStatusShipmentSh() == 'NÃO' || $this->_control->getStatusShipmentMg() == 'NÃO')) {
	            Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_shipment_mg','SIM');
	            
	            if (!$this->_apiOff && !$this->_hasShipment) {
					$items = $this->_order->getAllVisibleItems();
					$shipment = $this->_order->getShipmentsCollection()->getFirstItem();
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
						if($track->getTrackNumber()) {
							$code = $track->getTrackNumber();
							$carrier = ($track->getCarrierCode()) ? $track->getCarrierCode() : "custom";
							$methodTitle = $track->getTitle();
						
							$shipmentData['track'] = array(
								"code"      => $code,
								"carrier"   => $carrier,
								"method"    => $methodTitle,
							);
							
							$result = $this->_api->shipments($this->_order->getSkyhubCode(),array("shipment" => $shipmentData));
							$message = $this->_api->checkResponseErrors($result['httpCode']);
							if ($message) {
								Mage::log('SHIPMENT ERROR '.$this->_order->getSkyhubCode().': ' . $message,null,'onestic_skyhub.log');
								$this->_errors++;
							} else {
								Mage::log('ENTREGA DO PEDIDO '.$this->_order->getSkyhubCode().' CRIADA COM SUCESSO',null,'onestic_skyhub.log');
								Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_shipment_sh','SIM');
							}
						}
					}
				}
				
				// RECUPERA PLP
				//Mage::getModel('onestic_skyhub/order')->getShipmentLabel($this->_order->getSkyhubCode());
	        }
    	}
    }
    
    public function delivery($orderId=NULL, $onlyLocal=FALSE) {
    	if ($orderId) $this->_init($orderId, $onlyLocal);
    	if($this->_hasOrder && !$this->_apiOff) {
	        if($this->_order->getStatus() == 'entregue') {
	            Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_delivery_mg','SIM');
	            
	            if (!$this->_apiOff && !$this->_hasDelivered) {
					$result = $this->_api->delivery($this->_order->getSkyhubCode());
					$message = $this->_api->checkResponseErrors($result['httpCode']);
					if ($message) {
						Mage::log('DELIVERY ERROR '.$this->_order->getSkyhubCode().': ' . $message,null,'onestic_skyhub.log');
						$this->_errors++;
					} else {
						Mage::log('PEDIDO '.$this->_order->getSkyhubCode().' ENTREGUE COM SUCESSO',null,'onestic_skyhub.log');
						Mage::getModel('onestic_skyhub/orders')->update($this->_order->getSkyhubCode(),'status_delivery_sh','SIM');
					}
				}
	        }
    	}
    } 
    
    protected function _checkStatus() {
    	$canStatus = Mage::getModel('core/session')->getCanStatus();
    	if ($this->_hasControl && $this->_hasOrder && !$canStatus) {
    		if ($this->_order->getStatus() == 'pending' && in_array($this->_control->getStatusSkyhub(),array('aprovado','cancelado'))) {
    			Mage::getModel('core/session')->setCanStatus(true);
    			Mage::getModel('onestic_skyhub/order')->updateStatus($this->_control,$this->_control->getStatusSkyhub());
    			Mage::getModel('core/session')->unsCanStatus();
    		}
    	}
    }
}