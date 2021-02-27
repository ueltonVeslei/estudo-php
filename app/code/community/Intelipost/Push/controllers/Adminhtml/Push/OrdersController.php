<?php

class Intelipost_Push_Adminhtml_Push_OrdersController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("push/orders")->_addBreadcrumb(Mage::helper("adminhtml")->__("Orders Manager"),Mage::helper("adminhtml")->__("Orders Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Push"));
			    $this->_title($this->__("Manage Orders"));

				$this->_initAction();
				$this->renderLayout();
		}
		
		public function updateTrackingAction()
		{
			$order_increment_id = $this->getRequest()->getParam('increment_id');
			$order_id = $this->getRequest()->getParam('order_id');

			Mage::helper('basic')->setVersionControlData(Mage::helper('push')->getModuleName(), 'push');
			$intelipostApi = Mage::getModel('basic/intelipost_api');
		    $intelipostApi->apiRequest(Intelipost_Basic_Model_Intelipost_Api::GET, "shipment_order/{$order_increment_id}", false, Mage::helper('basic')->getVersionControlModel());

		    $response = $intelipostApi->decodeJsonResponse(true);

		    if (!$intelipostApi->_hasErrors)
		    {
		    			       
		    }
		    else
		    {
		    	foreach ($response ['messages'] as $message) Mage::getSingleton('adminhtml/session')->addError($message ['text']);
		    }
		    Mage::log($response);
			$this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id ));
		}

        public function sendAction()
        {
        	$params = $this->getRequest()->getParams();
        	$orderIds = $this->getRequest()->getParam('order_id');
        	$error_order_ids = array();
        	$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');
        	$sucess = false;
        	
        	if (!is_array($orderIds))
        	{
        		$order_id = $orderIds;
        		$orderIds = array();
        		$orderIds[] = $order_id;
        	}
		
        	if (count($orderIds) > 0 && !empty($orderIds[0]) )
        	{			         		
	            $orderCount = 0;
				foreach($orderIds as $Id) 
				{
					try 
			    	{
			    		if (isset($params['source'])) {
			    			$intelipost_basic_order = Mage::getModel('basic/orders')->load($Id, 'order_id');
			    		}
			    		else {                
	                		$intelipost_basic_order = Mage::getModel('basic/orders')->load($Id);
	                	}

				        $order = Mage::getModel('sales/order')->load($intelipost_basic_order->getOrderId());

				        if (!$order->hasInvoices())
				        {
					        throw new Mage_Shipping_Exception(Mage::helper('push')->__('Orders not invoiced. Please invoice orders before shipment request.'));
				        }

				        $tracking = Mage::getModel('basic/request_shipment_order');
				        $tracking->fetchTrackRequest($order, null);

				        Mage::helper('basic')->setVersionControlData(Mage::helper('push')->getModuleName(), 'push');

				        $intelipostApi = Mage::getModel('basic/intelipost_api');
				        $intelipostApi->apiRequest(Intelipost_Basic_Model_Intelipost_Api::POST, 'shipment_order', $tracking, Mage::helper('basic')->getVersionControlModel());

				        if (!$intelipostApi->_hasErrors)
	                	{
	                        $response = $intelipostApi->decodeJsonResponse(true);

	                        if(!strcmp($response['status'], 'OK'))
	                        {
	                        	$tracking_code = '';
	                        	if (isset($response['content']['shipment_order_volume_array'][0]))
	                        	{
	                        		$tracking_code = trim($response['content']['shipment_order_volume_array'][0]['tracking_code']);
	                        	}
								
								if (!$tracking_code)
								{
									$tracking_code = $order->getIncrementId();
								}
								/*
					            $track = Mage::getModel('sales/order_shipment_track');
					            $track->setNumber($response['content']['shipment_order_volume_array'][0]['tracking_code']) //tracking number / awb number
						            ->setCarrierCode('tracking') //carrier code
						            ->setTitle($order->getShippingDescription());*/
								
								$intelipost_tracking = Mage::getModel("basic/trackings")->load($order->getIncrementId(), 'increment_id');
						        	$data = array(  'increment_id' => $order->getIncrementId(),
						        					'code'		   => $tracking_code
						        					);
						        	$intelipost_tracking->addData($data);
						        	$intelipost_tracking->save();						        
					            /*
					            $track->setInvoiceSeries($param['invoice_series']);
					            $track->setInvoiceNumber($param['invoice_number']);
					            $track->setInvoiceKey($param['invoice_key']);
					            $track->setFederalTaxPayerId($param['federal_tax_payer_id']);
					            //$track->setStateTaxPayerId($param['state_tax_payer_id']);
						        
	                            
	                            $shipment = Mage::getModel ('sales/service_order', $order)->prepareShipment ();
	                            $shipment->addTrack($track);
	                            $shipment->register ();

	                            Mage::getModel ('core/resource_transaction')
	                                ->addObject ($shipment)
	                                ->addObject ($shipment->getOrder ())
	                                ->save ();*/
	                            
	                            $basic_order = Mage::getModel ('basic/orders')->load($order->getId(),'order_id');
	                            if(!empty($basic_order) && $basic_order->getId()>0)
	                            {
	                                $basic_order->setStatus('created');
	                                $basic_order->save();
	                                $sucess = true;

	                                if ($save_order_comments)
		                			{
		                				$user = Mage::getSingleton('admin/session');
										$userEmail = $user->getUser()->getEmail();
										$comment = 'Usuário: ' . $userEmail;

		                				$order->addStatusHistoryComment('[Intelipost] Criado na Intelipost - ' . $comment);
		                				$order->save();
		                			}
	                            }
	                        }

	                        $orderCount ++;
				        }	
				        else
				        {
					        throw new Mage_Shipping_Exception($intelipostApi->_arrErrors[0]->text);
				        }

				    }
				    catch (Exception $e) 
			    	{
			    		$error_order_ids[] = array('order_id' => $order->getIncrementId(), 'message' => $e->getMessage());
				    	//Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			    	}			    		                
			    }

			    if ($sucess) {
			    	Mage::getSingleton('adminhtml/session')->addSuccess(__(Mage::helper('push')->__("%d orders were successfully sent.", $orderCount))); 			   
				}
			    
			    if (count($error_order_ids) > 0)
			    {
			    	foreach ($error_order_ids as $errors) 
			    	{
			    		Mage::getSingleton('adminhtml/session')->addError(__(Mage::helper("push")->__('Order %d: ' . $errors['message'], $errors['order_id'])));
			    	}
			    }

		    } else
			    Mage::getSingleton('adminhtml/session')->addNotice(__("No order selected."));

			if (isset($params['source'])) {
				$this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id ));
			}
			else {
		    	$this->_redirect('*/*/index');
			}
        }

        public function updateNfeDataAction()
        {
        	$order_id = $this->getRequest()->getParam('order_id');
        	$order = Mage::getModel('sales/order')->load($order_id);
        	$intelipost_nfe = Mage::getModel('basic/nfes')->load($order->getIncrementId(), 'increment_id');
        	$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');        	

        	try
        	{
	        	if (!count($intelipost_nfe->getData()) > 0)
	        	{
	        		throw new Exception('[Intelipost] Nota fiscal não encontrada para este pedido.');
	        	}

	        	$update_nfe = Mage::getModel('basic/request_shipment_update_nfe');
				$update_nfe->fetchRequest($order, $intelipost_nfe->getData());

				Mage::helper('basic')->setVersionControlData(Mage::helper('push')->getModuleName(), 'push');
				$intelipostApi = Mage::getModel('basic/intelipost_api');
				$intelipostApi->apiRequest(Intelipost_Basic_Model_Intelipost_Api::POST, 'shipment_order/set_invoice', $update_nfe, Mage::helper('basic')->getVersionControlModel());
				if (!$intelipostApi->_hasErrors)
		        {
		        	 $response = $intelipostApi->decodeJsonResponse(true);
		        	 if ($save_order_comments)
					 {
					 	$user = Mage::getSingleton('admin/session');
						$userEmail = $user->getUser()->getEmail();
						$comment = 'Usuário: ' . $userEmail;

					 	$order->addStatusHistoryComment('[Intelipost] Adicionado NF na Intelipost - ' . $comment);
					 	$order->save();
					 }
		        	 
		        	 Mage::getSingleton("adminhtml/session")->addSuccess(__(Mage::helper("push")->__("[Intelipost] Dados da nota atualizados com sucesso.")));
		        }
		        else
		        {
		        	throw new Exception($intelipostApi->_arrErrors[0]->text);
		        }
	        }
	        catch(Exception $e)
	        {
	        	Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
	        }	
			
			$this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id ));
        }

        public function updateNfeData($order_id)
        {
        	$order = Mage::getModel('sales/order')->load($order_id);
        	$intelipost_nfe = Mage::getModel('basic/nfes')->load($order->getIncrementId(), 'increment_id');
        	$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');

        	if (!count($intelipost_nfe->getData()) > 0)
        	{
        		return array('order_id' => $order->getIncrementId(), 'message' => 'Nota fiscal não encontrada para este pedido.');
        	}

        	$update_nfe = Mage::getModel('basic/request_shipment_update_nfe');
			$update_nfe->fetchRequest($order, $intelipost_nfe->getData());

			Mage::helper('basic')->setVersionControlData(Mage::helper('push')->getModuleName(), 'push');
			$intelipostApi = Mage::getModel('basic/intelipost_api');
			$intelipostApi->apiRequest(Intelipost_Basic_Model_Intelipost_Api::POST, 'shipment_order/set_invoice', $update_nfe, Mage::helper('basic')->getVersionControlModel());
			if (!$intelipostApi->_hasErrors)
	        {
	        	 $response = $intelipostApi->decodeJsonResponse(true);
	        	 if ($save_order_comments)
				 {
				 	$user = Mage::getSingleton('admin/session');
					$userEmail = $user->getUser()->getEmail();
					$comment = 'Usuário: ' . $userEmail;

				 	$order->addStatusHistoryComment('[Intelipost] Adicionado NF na Intelipost - ' . $comment);
				 	$order->save();
				 }
	        	 return true;
	        }
	        else
	        {
	        	return array('order_id' => $order->getIncrementId(), 'message' => $intelipostApi->_arrErrors[0]->text);
	        }	
			
        }

        public function readyForShipmentAction()
        {
        	$params = $this->getRequest()->getParams();
        	$orderIds = $this->getRequest()->getParam('order_id');
        	$error_order_ids = array();
        	$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');
        	$sucess = false;

        	if (!is_array($orderIds))
        	{
        		$order_id = $orderIds;
        		$orderIds = array();
        		$orderIds[] = $order_id;
        	}

        	if (count($orderIds) > 0 && !empty($orderIds[0]) )
        	{			    
	            $orderCount = 0;
				foreach($orderIds as $Id) 
				{
					try 
			    	{			    		
				    	if (isset($params['source'])) {
			    			$intelipost_basic_order = Mage::getModel('basic/orders')->load($Id, 'order_id');
			    		}
			    		else {                
	                		$intelipost_basic_order = Mage::getModel('basic/orders')->load($Id);
	                	}

				    	if ($intelipost_basic_order->getStatus() == 'shipped') {
				    		throw new Exception("Can't set ready shipped orders");				    		
				    	}

				    	$intelipost_basic_nfes = Mage::getModel('basic/nfes')->load($intelipost_basic_order->getIncrementId(), 'increment_id');
				    	if (count($intelipost_basic_nfes->getData()) == 0)
        				{
        					$result = $this->updateNfeData($intelipost_basic_order->getOrderId());
        					if (is_array($result))
        					{
        						$error_order_ids[] = array('order_id' => $result['order_id'], 'message' => $result['message']);        						
        					}     
        					   					
        				}

				        $order = Mage::getModel('sales/order')->load($intelipost_basic_order->getOrderId());

				    	$ready_shipment_order = Mage::getModel('basic/request_shipment_ready_order');
				        $ready_shipment_order->fetchReadyShipmentOrderRequest($order->getIncrementId());

				        Mage::helper('basic')->setVersionControlData(Mage::helper('push')->getModuleName(), 'push');

				        $intelipostApi = Mage::getModel('basic/intelipost_api');
				        $intelipostApi->apiRequest(Intelipost_Basic_Model_Intelipost_Api::POST, 'shipment_order/ready_for_shipment', $ready_shipment_order, Mage::helper('basic')->getVersionControlModel());

				        if (!$intelipostApi->_hasErrors)
	                	{
	                        $response = $intelipostApi->decodeJsonResponse(true);
	                        if(!strcmp($response['status'], 'OK'))
	                        {
	                        	//$basic_order = Mage::getModel ('basic/orders')->load($Id,'order_id');
	                            if(!empty($intelipost_basic_order) && $intelipost_basic_order->getId()>0)
	                            {
	                                $intelipost_basic_order->setStatus('shipment ready');
	                                $intelipost_basic_order->save();
	                                $sucess = true;	      

	                                if ($save_order_comments)
				                	{
				                		$user = Mage::getSingleton('admin/session');
										$userEmail = $user->getUser()->getEmail();
										$comment = 'Usuário: ' . $userEmail;

				                		$order->addStatusHistoryComment('[Intelipost] Pronto para despacho na Intelipost - ' . $comment);
				                		$order->save();
				                	}                          
	                            }
	                        }
	                    }
	                    else
	                    {
	                    	Mage::log($intelipostApi->_arrErrors);
	                    	throw new Exception($intelipostApi->_arrErrors['0']->text);	                    	
	                    }
				    }
				    catch (Exception $e)
					{
						$error_order_ids[] = array('order_id' => $order->getIncrementId(), 'message' => $e->getMessage());
						//Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					}

					$orderCount++;
				}

				//Mage::getSingleton('adminhtml/session')->addSuccess(__("%d orders were successfully sent.", $orderCount)); 			   
			    
			    if ($sucess) {
			    	Mage::getSingleton("adminhtml/session")->addSuccess(__(Mage::helper("push")->__("%d orders shipments were ready for shipped.", $orderCount)));
			    }

			    if (count($error_order_ids) > 0)
			    {
			    	foreach ($error_order_ids as $errors) 
			    	{
			    		Mage::getSingleton('adminhtml/session')->addError(__(Mage::helper("push")->__('Order %d: ' . $errors['message'], $errors['order_id'])));
			    	}
			    }				
			}

			if (isset($params['source'])) {
				$this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id ));
			}
			else {
		    	$this->_redirect('*/*/index');
			}
        }
        public function shipmentAction ()
        {        
        	$params = $this->getRequest()->getParams();	
        	$use_gs_cron = Mage::getStoreConfig('intelipost_push/push_cron_config/use_gs_cron');        	

        	$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');
            $orders_ids = $this->getRequest()->getParam ('order_id');
            $utils = Mage::getModel ('push/utils');
            $error_order_ids = array();
            $sucess = false;
            $orderCount = 0;
            $update_status = false;

            if (!is_array($orders_ids))
        	{
        		$order_id = $orders_ids;
        		$orders_ids = array();
        		$orders_ids[] = $order_id;
        	}
                foreach ($orders_ids as $id => $value)
                {
                	try
                	{
                		if (isset($params['source'])) {
			    			$intelipost_basic_order = Mage::getModel('basic/orders')->load($value, 'order_id');
			    		}
			    		else {                
	                		$intelipost_basic_order = Mage::getModel('basic/orders')->load($value);
	                	}
	                   
	                    if ($intelipost_basic_order->getStatus() == 'shipped' || $intelipost_basic_order->getStatus() == 'waiting') {
	                    	throw new Exception("Can't set shipped order for 'waiting' or 'shipped' status");		
	                    }

	                    $order = Mage::getModel('sales/order')->load($intelipost_basic_order->getOrderId());

	                    if ($use_gs_cron)
			        	{
			        		$order_status = Mage::getStoreConfig('intelipost_push/attributes/gs_contingency_order_status');
			        		$order->setStatus($order_status);
			        		$order->save();

			        		$update_status = true;
			        		$orderCount++;
			        		continue;
			        	}

	                    if ($utils->generateShipment ($intelipost_basic_order->getOrderId()))
	                    {
	                        $utils->orderShipped ($intelipost_basic_order->getOrderId());
	                        $sucess = true;	                       

							if ($save_order_comments)
				            {
				            	$user = Mage::getSingleton('admin/session');
								$userEmail = $user->getUser()->getEmail();
								$comment = 'Usuário: ' . $userEmail;

				            	$order->addStatusHistoryComment('[Intelipost] Despachado na Intelipost - ' . $comment);
				            	$order->save();
				            }

	                    }
                	}
                	catch(Exception $e)
                	{
                		$order = Mage::getModel('sales/order')->load($intelipost_basic_order->getOrderId());
                		$error_order_ids[] = array('order_id' => $order->getIncrementId(), 'message' => $e->getMessage());
                	}

                	$orderCount++;
                }
                
                if ($sucess) 
                {
                	 Mage::getSingleton("adminhtml/session")->addSuccess(__(Mage::helper("push")->__("%d orders shipments were created successfully.", $orderCount)));
                }

                if ($update_status)
                {
                	Mage::getSingleton("adminhtml/session")->addSuccess(__(Mage::helper("push")->__("%d orders updated status successfully.", $orderCount)));
                }
                
                if (count($error_order_ids) > 0)
			    {
			    	foreach ($error_order_ids as $errors) 
			    	{
			    		Mage::getSingleton('adminhtml/session')->addError(__(Mage::helper("push")->__('Order %d: ' . $errors['message'], $errors['order_id'])));
			    	}
			    }		                
                
                if (isset($params['source'])) {
					$this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id ));
				}
				else {
			    	$this->_redirect('*/*/index');
				}
        }

        public function romaneioAction()
        {
        	$orders_ids = $this->getRequest()->getParam ('order_id');        	
        	$methods_info = array();

        	foreach ($orders_ids as $key => $value) 
        	{
        		$intelipost_order = Mage::getModel('basic/orders')->load($value);
        		$method_name = Mage::helper('basic')->getIntelipostMethodName($intelipost_order->getDeliveryMethodId(), 'push');
        		
        		$info = array('order_id' => $intelipost_order->getOrderId());

        		$this->array_push_key($methods_info, $method_name, $info);
        	}
			
			Mage::getSingleton('admin/session')->setIntelipostRomaneioMethods($methods_info);

			$this->_redirect('*/*/romaneioDetails');			
        }

        public function romaneioDetailsAction()
        {
        	$methods_info = Mage::getSingleton('admin/session')->getIntelipostRomaneioMethods();
        	Mage::getSingleton('admin/session')->unsIntelipostRomaneioMethods();
        	Mage::register('methods', $methods_info);

        	$this->loadLayout();
        	$this->renderLayout();
        }

        public function printRomaneioAction()
        {
        	$param = $this->getRequest()->getParams();
        	$order_ids = json_decode($param['data2']);
        	
        	$this->loadLayout ();

        	$this->getLayout ()->getBlock ('push_orders_romaneio')
        					->setOrdersIds ($order_ids)
        					->setShippingMethodName($param['data1']);

        	$this->renderLayout ();

			$content = $this->getLayout()->getBlock('content')->toHtml();
			$filename = $param['data1'] . '-' . Mage::getSingleton ('core/date')->date ('Y-m-d_H-i-s') . '.html'; 

			$filepath = Mage::helper('push')->createRomaneioPath($filename);
			$file_url = '';
			if ($filepath != '')
			{
				$myfile = fopen($filepath, "w") or die("Unable to open file!");

				fwrite($myfile, utf8_decode ($content));

				fclose($myfile);

				$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
				$path .= 'intelipost/push/romaneios/'.$filename;
				$file_url = $path;
				/*
				$file_url=str_replace('\\','/',$filepath);
				$file_url=str_replace($_SERVER['DOCUMENT_ROOT'],'',$file_url);
				$file_url='http://'.$_SERVER['HTTP_HOST'].$file_url;*/
			}

			$array = array('file' => $filename, 'filepath' => $file_url);
			$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
			$this->getResponse()->setBody(json_encode($array));
        }

        public function array_push_key(&$array, $key, $value) 
        {                
        	$array[$key][] = $value;
		}  

		public function orderListLinkAction()
		{
			$this->_redirectUrl('https://secure.intelipost.com.br/create-batch-files/');
		}

		protected function _isAllowed()
		{
			$session = Mage::getSingleton('admin/session');
			$resourceId = $session->getData('acl')->get('admin/system/config/orders')->getResourceId();
			return Mage::getSingleton('admin/session')->isAllowed($resourceId);
		}
}

