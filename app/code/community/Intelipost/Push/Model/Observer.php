<?php
/**
 * @category   Observer
 * @package    Intelipost_Push
 * @copyright  Copyright (c) 2014 Intelipost
 * @author     Intelipost <Intelipost.com.br>
 */
class Intelipost_Push_Model_Observer 
{
	public function blockToHtmlBefore(Varien_Event_Observer $observer)
	{
		$block = $observer->getEvent ()->getBlock ();
		$appendButton = Mage::getStoreConfigFlag ('intelipost_push/manage_ordes/order_button');

		if ($block->getType () == 'adminhtml/sales_order_view' && $appendButton && $this->canAppendButton())
		{
		    $block->addButton('intelipost_push', array(
		        'label'     => Mage::helper('push')->__('Criar Pedido IP'),
		        'onclick'   => "setLocation('" . $this->getCreateIntelipostOrderUrl() . "')",
		        'class'     => 'go'
		    ));
		}
	}

	public function afterInvoice(Varien_Event_Observer $observer)
	{
		$create_on_invoice = Mage::getStoreConfig('intelipost_push/general/create_on_invoice');

		if (!$create_on_invoice) {
			return;
		}

		$order = $observer->getPayment()->getOrder();		
		$error_order_ids = array();
		$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');

		try
		{
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
		           	
		           	$tracking_code = trim($response['content']['shipment_order_volume_array'][0]['tracking_code']);

					if (!$tracking_code)
					{
						$tracking_code = $order->getIncrementId();
					}					

			        $intelipost_tracking = Mage::getModel("basic/trackings");
			        $data = array(  'increment_id' => $order->getIncrementId(),
			        				'code'		   => $tracking_code
			        				);
			        $intelipost_tracking->addData($data);
			        $intelipost_tracking->save();
		                            
		            $basic_order = Mage::getModel ('basic/orders')->load($order->getId(),'order_id');
		            
		            if(!empty($basic_order) && $basic_order->getId()>0)
		            {		            	
		                $basic_order->setStatus('created');
		               	$basic_order->save();		
		               	                
		                Mage::helper('push')->log('intelipost order create: ' . $order->getIncrementId());
		                Mage::getSingleton('adminhtml/session')->addSuccess(__(Mage::helper('push')->__("[Intelipost - após fatura] %d pedido foi enviado com sucesso.", 1))); 

		                if ($save_order_comments)
		                {		                	
		                	$order->addStatusHistoryComment('[Intelipost - após fatura] Criado na Intelipost');
		                }

		    		}
		    	}
		    }
		    else
		    {
		    	Mage::helper('push')->log($intelipostApi->_arrErrors);
		    	throw new Mage_Shipping_Exception($intelipostApi->_arrErrors[0]->text);
		    }
		}
		catch(Exception $e)
		{
			$error_order_ids[] = array('order_id' => $order->getIncrementId(), 'message' => $e->getMessage());
			Mage::helper('push')->log('[Intelipost] order create error: ' . $e->getMessage());
		}

		if (count($error_order_ids) > 0)
		{
		   	foreach ($error_order_ids as $errors) 
		   	{
		   		Mage::helper('push')->log('[Intelipost] order create error: ' . $errors['order_id']);
		   		Mage::getSingleton('adminhtml/session')->addError(__(Mage::helper("push")->__('Order %d: [Intelipost] ' . $errors['message'], $errors['order_id'])));
		   	}
		}	

	}

	public function getCreateIntelipostOrderUrl()
	{
		$order = Mage::registry ('current_order');

		return Mage::helper('adminhtml')->getUrl('adminhtml/push_orders/send', array('order_id' => $order->getId (), 'source' => 'magento_order'));
	}

	public function canAppendButton()
	{
		$order = Mage::registry ('current_order');
		$status = $order->getStatus();
		
		$allowed_status = Mage::getStoreConfig('intelipost_push/manage_ordes/order_status');
		if ($allowed_status)
		{
			if (strpos($allowed_status, ',') !== false)
			{
				$allowed_status = explode(',', $allowed_status);
			}

			if (!is_array($allowed_status))
			{
				$text = $allowed_status;
				$allowed_status = array();
				$allowed_status[] = $text;
			}

			if (!in_array($status, $allowed_status))
			{
				return false;
			}
		}

		$intelipost_basic_order = Mage::getModel('basic/orders')->load($order->getId(), 'order_id');

		if (count($intelipost_basic_order->getData()) > 0)
		{
			if ($intelipost_basic_order->getStatus() == 'waiting') {
				return true;
			}
		}

		return false;
	}

	public function intelipostCreateShipOrder()
	{
		$error_order_ids = array();
		$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');
		$status = Mage::getStoreConfig('intelipost_push/push_cron_config/cso_order_status');		
		$canUpdate = Mage::getStoreConfig('intelipost_push/push_cron_config/use_cso_cron');
		$order_qty = Mage::getStoreConfig('intelipost_push/push_cron_config/cso_order_qty');
		$order_qty = $order_qty ? $order_qty : 40;

		if (strpos($status, ',') > 0) 
		{
            $status = explode(',', $status);
        }

		if ($canUpdate)
		{
			try
			{
				$expression = "main_table.status";

				$collection = Mage::getModel('sales/order')->getCollection()
							->addFieldToFilter($expression, array('in' => $status));

				$collection->getSelect()
							->join(	array('bo' => $collection->getTable('basic/orders')), 
		                    'main_table.entity_id = bo.order_id',
		                    array('bo.status' => 'bo.status'));
				
				if (Mage::getStoreConfig('intelipost_push/general/nfe_required_create_intelipost'))
				{
					$collection->getSelect()
							->join(array('nfe' => $collection->getTable('basic/nfes')),
							'main_table.increment_id=nfe.increment_id',
							array('nfe.created_at' => 'nfe.created_at'));		                    
				}

				$collection->getSelect()->where("bo.status LIKE ?", 'waiting');
		       	
		       	$collection->getSelect()->limit($order_qty);

		       	if (count($collection->getData()) > 0)
		       	{
		       		$orderCount = 0;
		       		
		       		foreach ($collection->getData() as $data) 
		       		{
		       			$order = Mage::getModel('sales/order')->load($data['entity_id']);

		       			if (!$order->hasInvoices())
				        {
				        	$error_order_ids[] = array('order_id' => $order->getIncrementId(), 'message' => Mage::helper('push')->__('Orders not invoiced. Please invoice orders before shipment request.'));	
							continue;
					        //throw new Mage_Shipping_Exception(Mage::helper('push')->__('Orders not invoiced. Please invoice orders before shipment request.'));
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
	                        	$tracking_code = trim($response['content']['shipment_order_volume_array'][0]['tracking_code']);
								
								if (!$tracking_code)
								{
									$tracking_code = $order->getIncrementId();
								}

								$intelipost_tracking = Mage::getModel("basic/trackings");
						        $data = array(  'increment_id' => $order->getIncrementId(),
						        					'code'		   => $tracking_code
						        					);
						        $intelipost_tracking->addData($data);
						        $intelipost_tracking->save();		

						        $basic_order = Mage::getModel ('basic/orders')->load($order->getId(),'order_id');
	                            if(!empty($basic_order) && $basic_order->getId()>0)
	                            {
	                                $basic_order->setStatus('created');
	                                $basic_order->save();

	                                if ($save_order_comments)
		                			{
		                				$order->addStatusHistoryComment('[Intelipost Cron] Criado na Intelipost');
		                				$order->save();
		                			}

		                			Mage::helper('push')->log('[Intelipost Cron] Pedido# ' . $order->getIncrementId() . ' criado.');
	                            }

	                            $orderCount++;
							}
						}
						else
		                {	                	
		                  	Mage::helper('push')->log('[Intelipost Cron CSO] Erro: ' . $intelipostApi->_arrErrors['0']->text . '. Pedido: ' . $order->getIncrementId());
		                }
		       		}

		       		Mage::helper('push')->log(Mage::helper("push")->__('[Intelipost Cron CSO] %d Pedidos de envio criados na Intelipost.', $orderCount));
		       	}
		       	else
		       	{
		       		Mage::helper('push')->log('Não foram encontrados pedidos para criar.');
		       	}
		    }
		    catch(Exception $e)
			{
				$error_order_ids[] = array('order_id' => $order->getIncrementId(), 'message' => $e->getMessage());			
			}
	        
	        if (count($error_order_ids) > 0)
			{
			   	foreach ($error_order_ids as $errors) 
			  	{
			  		Mage::helper('push')->log(Mage::helper("push")->__('Order %d: ' . $errors['message'], $errors['order_id']));
			   	}
			}
		}
	}

	public function intelipostReadyToShip()
	{
		$sucess = false;
		$error_order_ids = array();
		$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');
		$status = Mage::getStoreConfig('intelipost_push/push_cron_config/rts_order_status');		
		$canUpdate = Mage::getStoreConfig('intelipost_push/push_cron_config/use_rts_cron');
		$order_qty = Mage::getStoreConfig('intelipost_push/push_cron_config/rts_order_qty');
		$order_qty = $order_qty ? $order_qty : 40;

		if (strpos($status, ',') > 0) 
		{
            $status = explode(',', $status);
        }

		if ($canUpdate)
		{
			try
			{
				$expression = "main_table.status";

				$collection = Mage::getModel('sales/order')->getCollection()
							->addFieldToFilter($expression, array('in' => $status));

				$collection->getSelect()
							->join(	array('bo' => $collection->getTable('basic/orders')), 
		                    'main_table.entity_id = bo.order_id',
		                    array('bo.status' => 'bo.status'));

				/*$collection->getSelect()
							->join(array('nfe' => $collection->getTable('basic/nfes')),
							'main_table.increment_id=nfe.increment_id',
							array('nfe.created_at' => 'nfe.created_at'));*/

				$collection->getSelect()->where("bo.status LIKE ?", 'created');
		       	
		       	$collection->getSelect()->limit($order_qty);

		       	if (count($collection->getData()) > 0)
		       	{
		       		$orderCount = 0;
		       		foreach ($collection->getData() as $data) 
		       		{	       			
		       			$order = Mage::getModel('sales/order')->load($data['entity_id']);

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
		                      	$basic_order = Mage::getModel ('basic/orders')->load($order->getId(),'order_id');
		                        if(!empty($basic_order) && $basic_order->getId()>0)
		                        {
		                            $basic_order->setStatus('shipment ready');
		                            $basic_order->save();
		                            Mage::helper('push')->log('[Intelipost Cron RTS] Pedido# ' . $order->getIncrementId() . ' preparado para envio');	                                

		                            if ($save_order_comments)
					                {
					                	$order->addStatusHistoryComment('[Intelipost Cron] Pronto para despacho na Intelipost');
					                	$order->save();
					                }
		                        }
		                    }

		                    $orderCount++;
		                }
		                else
		                {	                	
		                  	Mage::helper('push')->log('[Intelipost Cron RTS] Erro: ' . $intelipostApi->_arrErrors['0']->text);
		                }	                
		       		}

		       		Mage::helper('push')->log(Mage::helper("push")->__('[Intelipost Cron RTS] %d Pedidos preparados para envio na Intelipost.', $orderCount));
		       	}
		       	else
		       	{
		       		Mage::helper('push')->log('Não foram encontrados pedidos para preparar o envio.');
		       	}	       
			}
			catch(Exception $e)
			{
				$error_order_ids[] = array('order_id' => $order->getIncrementId(), 'message' => $e->getMessage());			
			}
	        
	        if (count($error_order_ids) > 0)
			{
			   	foreach ($error_order_ids as $errors) 
			  	{
			  		Mage::helper('push')->log(Mage::helper("push")->__('Order %d: ' . $errors['message'], $errors['order_id']));
			   	}
			}
		}
	}

	public function intelipostGenerateShipment()
	{
		$sucess = false;
		$error_order_ids = array();
		$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');
		$status = Mage::getStoreConfig('intelipost_push/push_cron_config/gs_order_status');		
		$canUpdate = Mage::getStoreConfig('intelipost_push/push_cron_config/use_gs_cron');
		$utils = Mage::getModel ('push/utils');
		$order_qty = Mage::getStoreConfig('intelipost_push/push_cron_config/gs_order_qty');
		$order_qty = $order_qty ? $order_qty : 40;

		if (strpos($status, ',') > 0) 
		{
            $status = explode(',', $status);
        }

		if ($canUpdate)
		{
			try
			{
				$expression = "main_table.status";

				$collection = Mage::getModel('sales/order')->getCollection()
							->addFieldToFilter($expression, array('in' => $status));

				$collection->getSelect()
							->join(	array('bo' => $collection->getTable('basic/orders')), 
		                    'main_table.entity_id = bo.order_id',
		                    array('bo.status' => 'bo.status'));

				/*$collection->getSelect()
							->join(array('nfe' => $collection->getTable('basic/nfes')),
							'main_table.increment_id=nfe.increment_id',
							array('nfe.created_at' => 'nfe.created_at'));*/

				$collection->getSelect()->where("bo.status LIKE ?", 'shipment ready');

				$collection->getSelect()->limit($order_qty);

				if (count($collection->getData()) > 0)
		       	{
		       		$orderCount = 0;
		       		foreach ($collection->getData() as $data) 
		       		{
		       			Mage::helper('push')->log($data['increment_id']);

		       			$intelipost_basic_order = Mage::getModel ('basic/orders')->load ($data['entity_id'], 'order_id');

		       			Mage::helper('push')->log($data['generate shipment']);
		       			if ($utils->generateShipment ($intelipost_basic_order->getOrderId()))
	                    {
	                    	Mage::helper('push')->log($data['generate shipment s']);

	                        $utils->orderShipped ($intelipost_basic_order->getOrderId());
	                        Mage::helper('push')->log($data['order shipped']);
	                        $sucess = true;	                       

							if ($save_order_comments)
				            {
				            	$order = Mage::getModel('sales/order')->load($intelipost_basic_order->getOrderId());
				            	$order->addStatusHistoryComment('[Intelipost Cron] Despachado na Intelipost');
				            	$order->save();

				            	Mage::helper('push')->log('[Intelipost Cron GS] Pedido# ' . $order->getIncrementId() . ' - entrega gerada');
				            }

				            $orderCount++;
	                    }
	                    else
	                    {
	                    	Mage::helper('push')->log('[Intelipost Cron GS] Ocorreu um erro ao tentar gerar entrega.');
	                    }
		       		}

		       		Mage::helper('push')->log(Mage::helper("push")->__('[Intelipost GS] %d Pedidos geradas entregas na Intelipost.', $orderCount));
		       	}
		       	else
		       	{
		       		Mage::helper('push')->log('[Intelipost Cron GS] Erro: Não foram encontrados pedidos para gerar entregas.');
		       	}
			}
			catch(Exception $e)
			{
				Mage::helper('push')->log('[Intelipost Cron GS] Erro: ' . $e->getMessage());
			}
		}
	}

	public function updateNfe()
	{
		$canUpdate = Mage::getStoreConfig('intelipost_push/push_cron_config/use_nfe_cron');
		$save_order_comments = Mage::getStoreConfig('intelipost_push/general/save_on_order_comments');
		$order_qty = Mage::getStoreConfig('intelipost_push/push_cron_config/nfe_order_qty');
		$order_qty = $order_qty ? $order_qty : 40;


		if ($canUpdate)
		{

			$status = Mage::getStoreConfig('intelipost_push/push_cron_config/nfe_order_status');		
			$nf_comment = false;
			
			if (strpos($status, ',') > 0) 
			{
                $status = explode(',', $status);
            }

			try
			{
				$expression = "main_table.status";

				$collection = Mage::getModel('sales/order')->getCollection()
							->addFieldToFilter($expression, array('in' => $status));

				$collection->getSelect()
							->join(	array('bo' => $collection->getTable('basic/orders')), 
		                    'main_table.entity_id = bo.order_id',
		                    array('bo.status' => 'bo.status'));

				$collection->getSelect()
							->join(array('nfe' => $collection->getTable('basic/nfes')),
							'main_table.increment_id=nfe.increment_id',
							array('nfe.created_at' => 'nfe.created_at'));

				$collection->getSelect()->where("bo.status LIKE ?", 'created');

				$collection->getSelect()->limit($order_qty);
				
				if (count($collection->getData()) > 0)
		       	{
		       		$orderCount = 0;
		       		foreach ($collection->getData() as $data) 
		       		{	 
		       			$order = Mage::getModel('sales/order')->load($data['entity_id']);
		       			$commentsObject = $order->getStatusHistoryCollection(true);

		       			foreach ($commentsObject as $commentObj) 
		       			{
		       				if ($commentObj->getComment() == '[Intelipost Cron] Adicionado NF na Intelipost')
		       				{
		       					$nf_comment = true;
		       				}
		       			}

		       			if ($nf_comment)
		       			{
		       				$nf_comment = false;
		       				continue;
		       			}
		       			
		       			$intelipost_nfe = Mage::getModel('basic/nfes')->load($order->getIncrementId(), 'increment_id');
		       			
		       			$update_nfe = Mage::getModel('basic/request_shipment_update_nfe');
						$update_nfe->fetchRequest($order, $intelipost_nfe->getData());

						Mage::helper('basic')->setVersionControlData(Mage::helper('push')->getModuleName(), 'push');
						$intelipostApi = Mage::getModel('basic/intelipost_api');
						$intelipostApi->apiRequest(Intelipost_Basic_Model_Intelipost_Api::POST, 'shipment_order/set_invoice', $update_nfe, Mage::helper('basic')->getVersionControlModel());
						if (!$intelipostApi->_hasErrors)
		        		{
		        			Mage::helper('push')->log('Nota Pedido# ' . $order->getIncrementId() . ' enviado para Intelipost com sucesso.');

		        			if ($save_order_comments)
				            {
				              	$order->addStatusHistoryComment('[Intelipost Cron] Adicionado NF na Intelipost');
				              	$order->save();
				            }

				            $orderCount++;
		        		}
		        		else
		        		{
		        			Mage::helper('push')->log('[Intelipost Cron NFE] Erro: ' . $intelipostApi->_arrErrors[0]->text . ' - Pedido# ' . $order->getIncrementId());
		        		}
		       		}

		       		Mage::helper('push')->log(Mage::helper("push")->__('[Intelipost Cron NFE] %d Pedidos adicionados NF na Intelipost', $orderCount));
		       	}
		       	else
		       	{
		       		Mage::helper('push')->log('[Intelipost Cron NFE] Erro: Não foram encontradas notas para serem enviadas.');
		       	}
			}			
			catch(Exception $e)
			{
				Mage::helper('push')->log('[Intelipost Cron NFE] Erro: ' . $e->getMessage());
			}
		}
	}
}