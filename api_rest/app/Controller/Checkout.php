<?php
class Controller_Checkout extends Controller {

	// Revisa as informações da compra
	protected function _get() {
		if ($quoteID = $this->getData('ID')) {
			$mQuote = new Model_Quote($quoteID);
			/**
			 * Validação para não comprar produtos controlados
			 */
			$quote = $mQuote->getQuote();

			if (!$quote->getIsActive()) {
                $this->setResponse('status',Standard::STATUS500);
                $this->setResponse('data','Carrinho inválido');

                return false;
            }

			$quote->collectTotals();
			foreach ($quote->getAllVisibleItems() as $item) 
			{
				$product = $item->getProduct();
				$maxProdBuy = $product->getStockItem()->getMaxSaleQty();
				//echo 'nome: ' . $product->getName();
				//echo 'estoque maximo: ' . json_encode($maxProdBuy);
				if($maxProdBuy != 0 && $item->getQty() > $maxProdBuy)
				{
					$item->setQty($maxProdBuy);
					$item->save();
				}
				$categoriesIds = $product->getCategoryIds();
				//Proibe a compra de medicamento controlados
				//Após o termino do desenvolvimento do modulo sibrafar 
				//é necessário adicioar uma exceção
				if (in_array(26, $categoriesIds) || in_array(295, $categoriesIds)) { 
					$quote->removeItem($item->getId());
				}				
			} 
			$quote->save();
			if (!$mQuote->getQuote()->getDscToken()) {
                $this->setResponse('status',Standard::STATUS500);
                $this->setResponse('data','Carrinho inválido');

                return false;
            }

			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data',QuoteTransform::transform($mQuote));
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

            if (!$quote->getIsActive()) {
                $this->setResponse('status',Standard::STATUS500);
                $this->setResponse('data','Carrinho inválido');

                return false;
            }

			if (isset($body->address_id)) {
				$mQuote->setAddress('billing', $body->address_id);
				$mQuote->setAddress('shipping', $body->address_id);

				$mQuote2 = new Model_Quote($body->quote_id);
			}

			if (isset($body->customer_id)) {
				$customer = Mage::getModel('customer/customer')
					->load($body->customer_id);
				if($customer->getId()) {
				    if ($quote->getCustomer()->getId()) {
				        if ($quote->getCustomer()->getId() != $customer->getId()) {
                            $this->setResponse('status',Standard::STATUS500);
                            $this->setResponse('data','Dados inválidos');

                            return false;
                        }
                    }

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

            if (!$quote->getIsActive()) {
                $this->setResponse('status',Standard::STATUS500);
                $this->setResponse('data','Carrinho inválido');

                return false;
            }

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