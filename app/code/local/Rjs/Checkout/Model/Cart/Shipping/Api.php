<?php
include('Mage/Checkout/Model/Cart/Shipping/Api.php');


class Rjs_Checkout_Model_Cart_Shipping_Api extends Mage_Checkout_Model_Cart_Shipping_Api
{
    
public function setShippingMethod($quoteId, $shippingMethod, $store = null,$price = null) {
		
		$quote = $this->_getQuote($quoteId, $store);
		
		$quoteShippingAddress = $quote->getShippingAddress();
		if(is_null($quoteShippingAddress->getId()) ) {
			$this->_fault("shipping_address_is_not_set");
		}
	
		if (isset($price)) {
			 
			$sessionId = $this->_getSession()->getSessionId();
	
			$session = Mage::getSingleton("api/session");
			$session->setData('customShippingRate'.$sessionId, $price);
	
			$quote->getShippingAddress()->setCollectShippingRates(true);
		}
		$rate = $quote->getShippingAddress()->collectShippingRates()->getShippingRateByCode($shippingMethod);
		if (!$rate) {
			$this->_fault('shipping_method_is_not_available');
		}
	
		try {
			$quote->getShippingAddress()->setShippingMethod($shippingMethod);
			$quote->collectTotals()->save();
		} catch(Mage_Core_Exception $e) {
			$this->_fault('shipping_method_is_not_set', $e->getMessage());
		}
	
		return true;
	}

   

}
