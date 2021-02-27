<?php
class Controller_Coupon extends Controller {

	// Inclui cupom de desconto
	protected function _post() {
		if ($body = $this->getData('body')) {
			$coupon = Mage::getModel('salesrule/coupon')->load(trim($body->coupon), 'code');
			$rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
			if (!$rule->getId()) {
				$this->setResponse('status',Standard::STATUS404);
				$this->setResponse('data','Cupom inválido');
				return false;
			}

			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();
			$quote->setCouponCode($body->coupon);
			$quote->setTotalsCollectedFlag(false)->collectTotals();
	        $quote->collectTotals();
	        $quote->save();

			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data', QuoteTransform::transform($mQuote));
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

	// Exclui cupom de desconto
	protected function _delete() {
		if ($body = $this->getData('body')) {
			$mQuote = new Model_Quote($body->quote_id);
			$quote = $mQuote->getQuote();
			$quote->setCouponCode('');
			$quote->setTotalsCollectedFlag(false)->collectTotals();
	        $quote->collectTotals();
	        $quote->save();

			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data', QuoteTransform::transform($mQuote));
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

}