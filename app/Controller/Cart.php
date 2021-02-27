<?php
class Controller_Cart extends Controller {

	// Consulta os dados do carrinho
	protected function _get() {
		if ($quoteID = $this->getData('ID')) {
			$mQuote = new Model_Quote($quoteID);
			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data',$mQuote->getQuoteData());
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

	// Adiciona produto ao carrinho
	// Paramentros esperados:
	// body {
	// quote_id: ID, (opcional)
	// customer_id: ID, (opcional)
	// product_id: ID, (id do produto)
	// qty: qtde, (quantidade)
	// options: [OBJ OPTIONS] (opcional)
	//}
	protected function _post() {

		if ($body = $this->getData('body')) {
			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();

			if (isset($body->customer_id)) {
				$customer = Mage::getModel('customer/customer')
								->load($body->customer_id);
				if($customer->getId()) {
					Mage::getSingleton('customer/session')->setCustomer($customer);
					$quote->assignCustomer($customer);
					$quote->save();
				}
			}

			$product = Mage::getModel('catalog/product')->load($body->product_id);
			if ($product->getId()) {
				$buyInfo = array('qty' => $body->qty, 'product_id' => $body->product_id);
				try {
					$quote->addProduct($product, new Varien_Object($buyInfo));

					$quote->setIsActive(false)->setIsMultiShipping(false);
					$quote->getBillingAddress();
					$quote->getShippingAddress()->setCollectShippingRates(true);
					$quote->setTotalsCollectedFlag(false)->collectTotals();
					$quote->collectTotals()->save();

					$this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data', $mQuote->getQuoteData());
				} catch(Exception $e) {
					$this->setResponse('status',Standard::STATUS500);
					$this->setResponse('data','Ocorreu um erro: ' . $e);
				}
			} else {
				$this->setResponse('status',Standard::STATUS404);
				$this->setResponse('data','Produto não encontrado');
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data',['Dados não enviados']);
		}
	}

	// Atualiza quantidade do carrinho
	// Paramentros esperados:
	// body {
	// quote_id: ID, (opcional)
	// product_id: ID, (id do produto)
	// qty: qtde, (quantidade)
	//}
	protected function _put() {
		if ($body = $this->getData('body')) {
			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();

			$product = Mage::getModel('catalog/product')->load($body->product_id);
			if ($product->getId()) {
				$item = $quote->getItemByProduct($product);

				if($body->qty != $item->getQty()) {
					$item->setQty((double)$body->qty);
					$item->save();
					//$quote->collectTotals()->save();
					$quote->setTotalsCollectedFlag(false)->collectTotals();
					$quote->save();

					$this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data', $mQuote->getQuoteData());
				} else {
					$this->setResponse('status',Standard::STATUS500);
					$this->setResponse('data','Quantidade sem alteração');
				}

			} else {
				$this->setResponse('status',Standard::STATUS404);
				$this->setResponse('data','Produto não encontrado');
				return false;
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

	// Exclui produto do carrinho
	// Paramentros esperados:
	// body {
	// quote_id: ID, (opcional)
	// product_id: ID (id do produto)
	protected function _delete() {
		if ($body = $this->getData('body')) {
			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();

			$product = Mage::getModel('catalog/product')->load($body->product_id);
			if ($product->getId()) {
				$item = $quote->getItemByProduct($product);
				$quote->removeItem($item->getId());
				$quote->setTotalsCollectedFlag(false)->collectTotals();
				$quote->save();
				$this->setResponse('status',Standard::STATUS200);
				$this->setResponse('data', $mQuote->getQuoteData());
			} else {
				$this->setResponse('status',Standard::STATUS404);
				$this->setResponse('data','Produto não encontrado');
				return false;
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

}