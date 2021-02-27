<?php

class Intelipost_Quote_Adminhtml_Quote_QuoteController extends Mage_Adminhtml_Controller_Action
{
	public $order;

	public function requestQuoteAction()
	{
		try
		{
			$orderId = $this->getRequest()->getParam('order_id');		

			$intelipost_quote_id = $this->getRequest()->getParam('intelipost_quote_id');

			$request = Mage::getModel('quote/quote_request');
		    $request->processRequestQuote($orderId, $intelipost_quote_id);
		}	
		catch(Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$redirect = Mage::helper("adminhtml")->getUrl('adminhtml/sales_order/view/', array("order_id"=>$orderId));
		Mage::log($redirect);
		Mage::app()->getFrontController()->getResponse()->setRedirect($redirect);		
	}

	public function massRequoteAction()
	{
		$orderIds = $this->getRequest()->getParam('order_ids');

		try 
		{			
			$results = array();

			foreach($orderIds as $Id) 
			{
				$order = Mage::getModel('sales/order')->load($Id);
				$status = $order->getStatus();

			    if (!Mage::helper('quote')->isRequoteAllowed($status)) {
			       	throw new Mage_Shipping_Exception(Mage::helper('quote')->__('Requote canceled. Order# %d status is not allowed.', $order->getIncrementId()));
			    }

			    $info = new Varien_Object ();

			    $info->setOrderId($Id);
			    $intelipostBasicOrders = Mage::getModel('basic/orders')->load($Id, 'order_id');
			    $quoteId = count($intelipostBasicOrders->getData()) > 0 ? $intelipostBasicOrders->getDeliveryQuoteId() : 0;
			    $info->setIntelipostQuoteId($quoteId);

			    $results[] = $info->getData();
			}

			$orderCount = 0;

			foreach ($results as $result) 
			{
				$request = Mage::getModel('quote/quote_request');
				$request->processRequestQuote($result['order_id'], $result['intelipost_quote_id']);
				$orderCount++;
			}
			
			Mage::getSingleton('core/session')->addSuccess(Mage::helper('quote')->__('%d orders requote were successfully sent', $orderCount));	
		}
		catch(Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}


		$redirect = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/index');		
		Mage::app()->getFrontController()->getResponse()->setRedirect($redirect);
	}

	public function updateQuoteAction()
	{	
		$order_id = $this->getRequest()->getParam('order_id');
		$intelipost_quote_id = $this->getRequest()->getParam('intelipost_quote_id');
		
		$order = Mage::getModel('sales/order')->load($order_id, 'increment_id');
		$mudou_metodo = false;

		$request = Mage::getModel('quote/quote_request');
		$current_delivery_days = $request->getDeliveryDays($order->getShippingDescription());

		$intelipost_method = '';
		if ($this->getRequest()->getParam('shipping_methods')) {
			$intelipost_method = $this->getRequest()->getParam('shipping_methods');
		}

		if (strpos($intelipost_method, 'intelipost') !== false)
		{
			if ($order->getShippingMethod() != $intelipost_method)
			{
				$mudou_metodo = true;
			}

			$request = Mage::getModel('quote/quote_request');
			$method_id = $request->getMethodId($intelipost_method);
			$method_name = Mage::helper('basic')->getIntelipostMethodName($method_id, 'quote');

			$ip_basic_orders = Mage::getModel('basic/orders')->load($order->getId(), 'order_id');
			$ip_basic_orders->setDeliveryMethodId($method_id);
			$ip_basic_orders->save();

			$comment = '[Intelipost] - Forma de envio alterada. De: ' . $order->getShippingDescription() . ' - Para: ' . $method_name;
			$order->addStatusHistoryComment($comment);

			if (Mage::helper('quote')->getConfigData('concat_quote_id')) {
				$intelipost_method .= '_' . $intelipost_quote_id;
			}

			$order->setShippingMethod($intelipost_method);

			if (!Mage::helper('quote')->getConfigData('keep_method_description')) {
				$order->setShippingDescription(Mage::helper('quote')->getConfigData('title') . ' - ' . Mage::helper('quote')->getCustomizeCarrierTitle($method_name, $current_delivery_days));	
			}

			$order->save();

		}
		else
		{
			$method_id = $this->getRequest()->getParam('other_method_id');
			if (!is_numeric($method_id))
			{
				Mage::getSingleton('core/session')->addError(Mage::helper('quote')->__('[Intelipost] É necessário informar o código do método.'));	
			}
			else
			{
				$mudou_metodo = true;

				$ip_basic_orders = Mage::getModel('basic/orders')->load($order->getId(), 'order_id');
				if (count($ip_basic_orders->getData()) > 0)
				{
					$ip_basic_orders->setDeliveryMethodId($method_id);
					$ip_basic_orders->save();
				}

				$comment = '[Intelipost] - Forma de envio alterada. De: ' . $order->getShippingDescription() . ' - Para: ' . $this->getRequest()->getParam('other_method');
				$order->addStatusHistoryComment($comment);

				if (!Mage::helper('quote')->getConfigData('keep_method_description')) {
					$order->setShippingDescription($this->getRequest()->getParam('other_method'));
				}
				//$order->setShippingMethod(0);
				$order->save();
			}
		}

		if ($mudou_metodo)
		{
			$user = Mage::getSingleton('admin/session');
			$userEmail = $user->getUser()->getEmail();

			$comment = '[Intelipost] - Forma de envio alterada pelo usuário: ' . $userEmail;
			$order->addStatusHistoryComment($comment);
			$order->save();

			Mage::getSingleton('core/session')->addSuccess(Mage::helper('quote')->__('[Intelipost] Método de entrega alterado com sucesso.'));	
		}
	}

	public function updateVolumesAction()
	{
		$qty_volumes = $this->getRequest()->getParam('qty_volumes');
		$order_id = $this->getRequest()->getParam('order_id');

		if (is_numeric($qty_volumes))
		{
			$intelipost_orders = Mage::getModel('basic/orders')->load($order_id, 'order_id');
			if (count($intelipost_orders) > 0)
			{
				if ($qty_volumes != $intelipost_orders->getQtyVolumes())
				{
					$intelipost_orders->setQtyVolumes($qty_volumes);
					$intelipost_orders->save();

					Mage::getSingleton('core/session')->addSuccess(Mage::helper('quote')->__('[Intelipost] Volumes da cotação atualizados com sucesso.'));	
				}
			}
		}
	}

	public function createOrderAction()
	{
		$order_id = $this->getRequest()->getParam('data1');

		$this->_redirect('push/adminhtml_orders/send', array('order_id' => $order_id, 'source' => 'magento_order'));
	}
	
	public function updateStatusAction()
	{		
		$order_id = $this->getRequest()->getParam('order_id');
		$order = Mage::getModel('sales/order')->load($order_id, 'increment_id');

		$order_status = $this->getRequest()->getParam('order_statuses');
		$intelipost_status = $this->getRequest()->getParam('intelipost_statuses');

		$user = Mage::getSingleton('admin/session');
		$userEmail = $user->getUser()->getEmail();

		$comment = 'Usuário: ' . $userEmail;

		if ($order_status)
		{
			if ($order->getStatus() != $order_status)
			{
				$order->setStatus($order_status);
				$order->addStatusHistoryComment(Mage::helper('quote')->__('[Intelipost] Status Magento alterado com sucesso. - ' . $comment));
				$order->save();

				Mage::getSingleton('core/session')->addSuccess(Mage::helper('quote')->__('[Intelipost] Status Magento alterado com sucesso.'));	
			}
		}
		
		if ($intelipost_status)
		{
			$intelipost_order = Mage::getModel('basic/orders')->load($order->getId(), 'order_id');
			if (count($intelipost_order->getData()) > 0)
			{
				if ($intelipost_order->getStatus() != $intelipost_status)
				{
					$current_status = $intelipost_order->getStatus();
					$intelipost_order->setStatus($intelipost_status);
					$intelipost_order->save();

					$order->addStatusHistoryComment(Mage::helper('quote')->__('[Intelipost] Status Intelipost alterado com sucesso. De: ' .$current_status. ' - Para: ' .$intelipost_status. ' - ' . $comment));
					$order->save();

					Mage::getSingleton('core/session')->addSuccess(Mage::helper('quote')->__('[Intelipost] Status Intelipost alterado com sucesso.'));	
				}
			}
		}
	}

	protected function _isAllowed()
	{
		return true;
	}
}

