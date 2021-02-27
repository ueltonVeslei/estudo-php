<?php
class Controller_Gift extends Controller {

	// Obtem os produtos de venda agregada
	protected function _get() {
		if ($quoteID = $this->getData('ID')) {
			/**Trecho de código responsável pelo carregamento do bloco */
			Mage::app()->loadArea('frontend');
			$layout = Mage::getSingleton('core/layout');
			//load default xml layout handle and generate blocks
			$layout->getUpdate()->load('default');
			$layout->generateXml()->generateBlocks();
			$block = Mage::app()->getLayout()->createBlock('ampromo/items');
			
			/**Obtem a quote*/
			$quote = Mage::getModel('sales/quote')->load($quoteID);
			$quotedt = $quote->getData();
			
			$session = Mage::getSingleton('checkout/session');
			$session = $session->setQuoteId($quoteID);

			$products = $block->getItemsByRule();

			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data', $products->getData());
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
		
		$this->setResponse('status',Standard::STATUS200);
		$this->setResponse('data','');
	}

}