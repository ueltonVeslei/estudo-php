<?php
class Controller_Cart extends Controller {

	// Consulta os dados do carrinho
	protected function _get() {
		if ($quoteID = $this->getData('ID')) {
			$mQuote = new Model_Quote($quoteID);
			$data = json_decode(json_encode($mQuote->getQuoteData()), true);

			foreach ($data['items'] as $key => $item) {
				$image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item['product']['product_id'], 'image', Mage::app()->getStore()->getId());

				if($image) {
					$item['product']['image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
				}
				$data['items'][$key] = $item;
			}
			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data',$data);
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
			}

			$product = Mage::getModel('catalog/product')->load($body->product_id);

			if ($product->getId()) {
				$buyInfo = array('qty' => $body->qty, 'product_id' => $body->product_id);

				if ($body->options) {
				    foreach ($body->options as $option => $value) {
                        $buyInfo['super_attribute'][$option] = $value;
                    }
                }

				try {
					$quote->addProduct($product, new Varien_Object($buyInfo));

					$quote->setIsActive(false)->setIsMultiShipping(false);
					$quote->getBillingAddress();
					$quote->getShippingAddress()->setCollectShippingRates(true);
					$quote->setTotalsCollectedFlag(false)->collectTotals();
					$quote->collectTotals()->save();

					$data = json_decode(json_encode($mQuote->getQuoteData()), true);

					foreach ($data['items'] as $key => $item) {
						$image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item['product']['product_id'], 'image', Mage::app()->getStore()->getId());

						if($image) {
							$item['product']['image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
						}
						$data['items'][$key] = $item;
					}

					$this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data', $data);
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
				foreach($quote->getAllItems() as $item){
					if($item->getProductId() == $body->product_id){
						$item->setQty((double)$body->qty);
						$item->save();

						$quote->setTotalsCollectedFlag(false)->collectTotals();
						$quote->save();

						$data = json_decode(json_encode($mQuote->getQuoteData()), true);

						foreach ($data['items'] as $key => $item) {
							$image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item['product']['product_id'], 'image', Mage::app()->getStore()->getId());

							if($image) {
								$item['product']['image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
							}
							$data['items'][$key] = $item;
						}
						$this->setResponse('status',Standard::STATUS200);
						$this->setResponse('data', $data);
						return;
					}
				}
			} else {
				$this->setResponse('status',Standard::STATUS404);
				$this->setResponse('data','Produto não encontrado');
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

				if (!$item) {
				    $items = $quote->getAllVisibleItems();
				    foreach ($items as $it) {
				        if ($it->getProductId() == $body->product_id) {
				            $item = $it;
				            break;
                        }
                    }
                }

				if ($item) {
                    $quote->removeItem($item->getId());
                    $quote->setTotalsCollectedFlag(false)->collectTotals();
                    $quote->save();

                    $data = json_decode(json_encode($mQuote->getQuoteData()), true);

                    foreach ($data['items'] as $key => $item) {
                        $image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item['product']['product_id'], 'image', Mage::app()->getStore()->getId());

                        if($image) {
                            $item['product']['image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
                        }
                        $data['items'][$key] = $item;
                    }

                    $this->setResponse('status',Standard::STATUS200);
                    $this->setResponse('data', $data);
                } else {
                    $this->setResponse('status',Standard::STATUS404);
                    $this->setResponse('data','Produto não encontrado');
                    return false;
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

}
