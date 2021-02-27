<?php
class Controller_Shipping extends Controller {

	// Recuperar os valores de entrega
	protected function _put() {
		if ($body = $this->getData('body')) {
			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();
			$address = $quote->getShippingAddress();


			if (!$address->getPostcode()) {
				$address->setPostcode($body->postcode)
					->setCountryId($body->country_id);
			}

			$address->setCollectShippingRates(true);
			$quote->collectTotals()->save();

			$rates = [];
			foreach($address->getGroupedAllShippingRates() as $carrier) {
				foreach($carrier as $method) {
					$rates[] = [
						'code'			=> $method->getCode(),
						'carrier'		=> $method->getCarrierTitle(),
						'method'		=> $method->getMethodTitle(),
						'error'			=> $method->getErrorMessage(),
						'price'			=> $method->getPrice(),
						'order'			=> $method->getRateId()
					];
				}
			}

			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data',$rates);
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não informados');
		}
	}

	// Indica o método de entrega selecionado
	protected function _post() {
		if ($body = $this->getData('body')) {
			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();

			// adiciona o endereço, poupando uma request
			$address = Mage::getModel('customer/address')->load($body->address_id);
			$address->setIsDefaultBilling('1');
        	$address->setIsDefaultShipping('1');
        	$address->save();

			$quote->setBillingAddress($address);
			$quote->setShippingAddress($address);
			$quote->save();

			if (isset($body->shipping_method)) {
				$rate = $quote->getShippingAddress()->getShippingRateByCode($body->shipping_method);
		        if (!$rate) {
		        	$this->setResponse('status',Standard::STATUS500);
					$this->setResponse('data','Método de entrega inválido');
					return;
				}

		        $quote->getShippingAddress()->setShippingMethod($body->shipping_method);
		        try {
		        	$quote->collectTotals()->save();
		        	$this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data',$mQuote->getQuoteData());
		        } catch(Exception $e) {
					$this->setResponse('status',Standard::STATUS500);
					$this->setResponse('data','ERRO: ' . $e->getMessage());
				}
			} else {
				$this->setResponse('status',Standard::STATUS500);
				$this->setResponse('data','Método de entrega não informado');
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não informados');
		}
	}

}