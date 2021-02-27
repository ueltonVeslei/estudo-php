<?php
class Controller_Checkoutlocal extends Controller {

	// Revisa as informações da compra
	protected function _get() {
		if ($quoteID = $this->getData('ID')) {
			$mQuote = new Model_Quote($quoteID);

			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data',QuoteTransformLocal::transform($mQuote));
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

	// Indica o endereço usado no checkout
	protected function _put() {
		if ($body = $this->getData('body')) {
			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();
			if (isset($body->address_id)) {
				$mQuote->setAddress('billing', $body->address_id);
				$mQuote->setAddress('shipping', $body->address_id);

				$mQuote2 = new Model_Quote($body->quote_id);
			}

			if (isset($body->customer_id)) {
				$customer = Mage::getModel('customer/customer')
					->load($body->customer_id);
				if($customer->getId()) {
					Mage::getSingleton('customer/session')->setCustomer($customer);
					$quote->assignCustomer($customer);
					$quote->save();
				}

				$mQuote2 = new Model_Quote($body->quote_id);
			}
			// if ($body->address_type == 'billing') {
				
			// }

			$this->setResponse('status', Standard::STATUS200);
			$this->setResponse('data', QuoteTransform::transform($mQuote2));
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

	// Confirma a compra e transforma o quote em order
	protected function _post() {
		if ($body = $this->getData('body')) {
			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();
			try {

				$quote->setIsActive(false)->setIsMultiShipping(false);
				$quote->getBillingAddress();
				$quote->getShippingAddress()->setCollectShippingRates(true);
				$quote->collectTotals()->save();

				$onepage = Mage::getSingleton('checkout/type_onepage');
                $onepage->setQuote($quote);

                $onepage->getCheckout()->setCustomer($quote->getCustomer());

				$orderID = $onepage->saveOrder()->getLastOrderId();
				$order = Mage::getModel('sales/order')->loadByIncrementId($orderID);

				// $service = Mage::getModel('sales/service_quote', $quote);

		  //       $service->submitAll();

		  //       $mage_order = $service->getOrder();
		  //       $mage_order->save();

		   //      if($mage_order->getId()) {
		   //          $mage_order->getResource()->updateGridRecords($mage_order->getId());
		   //      } else {
		   //      	$this->setResponse('status',Standard::STATUS500);
					// $this->setResponse('data','ERRO DESCONHECIDO');
		   //      }

		        $this->setResponse('status',Standard::STATUS200);
				$this->setResponse('data',$order->getData());
			} catch(Exception $e) {
				$this->setResponse('status',Standard::STATUS500);
				$this->setResponse('data',$e->getMessage());
			}
	    }
	}

}