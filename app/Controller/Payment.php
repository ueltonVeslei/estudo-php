<?php
class Controller_Payment extends Controller {

	// Retornar as formas de pagamento
	protected function _get() {
		if ($quoteID = $this->getData('ID')) {
			$mQuote = new Model_Quote($quoteID);
			$quote = $mQuote->getQuote();
			//$payments = Mage::getSingleton('payment/config')->getActiveMethods();
			$helper = Mage::helper('payment');
			$helper->setLayout(Mage::app()->getLayout());
			foreach ($helper->getStoreMethods(Config::STORE) as $payment) {
				$instance = $helper->getMethodInstance($payment->getCode());
				$instance->setInfoInstance($quote->getPayment());
				$formBlock = $helper->getMethodFormBlock($instance);
				if ($formBlock) {
					$formBlock->setQuote($quote);
					$formBlock = $formBlock->toHtml();
				}
			    $methods[] = array(
			        'label'   	=> $payment->getTitle(),
			        'code' 		=> $payment->getCode(),
			        'html'		=> $formBlock
			    );
			}
			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data',$methods);
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não informados');
		}
	}

	// Indica o método de pagamento selecionado
	protected function _post() {
		if ($body = $this->getData('body')) {
			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();

			if (isset($body->payment_data)) {
				$method = $body->payment_data->method;
				if (!$quote->isVirtual())
		        	$quote->getShippingAddress()->setPaymentMethod($method);
		        else
		            $quote->getBillingAddress()->setPaymentMethod($method);

				$quote->getPayment()->importData((array)$body->payment_data);
				try {
					$quote->save();
					$this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data',$mQuote->getQuoteData());
				} catch(Exception $e) {
					$this->setResponse('status',Standard::STATUS500);
					$this->setResponse('data','ERRO: ' . $e->getMessage());
				}
			} else {
				$this->setResponse('status',Standard::STATUS500);
				$this->setResponse('data','Dados de pagamento não informados');
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não informados');
		}
	}

}