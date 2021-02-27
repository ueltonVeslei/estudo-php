<?php
class Controller_Checkout extends Controller {

	// Revisa as informações da compra
	protected function _get() {
		if ($quoteID = $this->getData('ID')) {
			$mQuote = new Model_Quote($quoteID);
			$quote = $mQuote->getQuoteData();
			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data',$quote);
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
			// if ($body->address_type == 'billing') {
				$mQuote->setAddress('billing', $body->address_id);
				$mQuote->setAddress('shipping', $body->address_id);

				$mQuote2 = new Model_Quote($body->quote_id);
			// }

			$this->setResponse('status', Standard::STATUS200);
			$this->setResponse('data', $mQuote2->getQuoteData());
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

				$service = Mage::getModel('sales/service_quote', $quote);

		        $service->submitAll();

		        $mage_order = $service->getOrder();
		        $mage_order->save();

		        if($mage_order->getId()) {
		            $mage_order->getResource()->updateGridRecords($mage_order->getId());
		        } else {
		        	$this->setResponse('status',Standard::STATUS500);
					$this->setResponse('data','ERRO DESCONHECIDO');
		        }
		        $quoteData = $mQuote->getQuoteData();
		        $this->setResponse('status',Standard::STATUS200);
				$this->setResponse('data',$mage_order->toArray());
			} catch(Exception $e) {
				$this->setResponse('status',Standard::STATUS500);
				$this->setResponse('data',$e->getMessage());
			}
	    }
	}

}