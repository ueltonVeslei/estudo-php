<?php
class Model_Quote {

	protected $_quote		= NULL;
	protected $_quoteID		= NULL;

	public function __construct($quoteID) {
		$this->_quoteID = $quoteID;
	}

	public function getQuoteData() {
		$quote = $this->_getQuote();
		$result = [
			'quote_id'				=> $quote->getId(),
			'coupon_code'			=> $quote->getCouponCode(),
			'reserved_order_id'		=> $quote->getReservedOrderId(),
			'items_count'			=> $quote->getItemsCount(),
			'items_qty'				=> $quote->getItemsQty(),
			'customer'				=> $quote->getCustomer()->toArray(),
			'totals'				=> $this->_getTotals(),
			'shipping'				=> $this->_getShipping(),
			'addresses'				=> (object)[
					'shipping'			=> $this->_getAddress('shipping'),
					'billing'			=> $this->_getAddress('billing'),
				],
			'items'					=> $this->_getItems()
		];

		return (object)$result;
	}

	public function getQuote() {
		return $this->_getQuote();
	}

	protected function _getQuote() {
		if (!$this->_quote) {
			// Patch para resolver bug de validação de SESSION
			Mage::getSingleton('core/cookie')->setLifetime(0);
			$store = Mage::getSingleton('core/store')->load(Config::STORE);
			$this->_quote = Mage::getModel('sales/quote')->setStore($store);
			if ($this->_quoteID) {
				//var_dump($this->_quoteID);
				$this->_quote = $this->_quote->load($this->_quoteID);
			}
		}

		return $this->_quote;
	}

	protected function _getAddress($type) {
		if ($type == 'billing') {
			$address = $this->_getQuote()->getBillingAddress();
		} else {
			$address = $this->_getQuote()->getShippingAddress();
		}
		$result = [
			'firstname'		=> $address->getFirstname(),
			'lastname'		=> $address->getLastname(),
			'street1'		=> $address->getStreet1(),
			'street2'		=> $address->getStreet2(),
			'street3'		=> $address->getStreet3(),
			'street4'		=> $address->getStreet4(),
			'region'		=> $address->getRegion(),
			'region_id'		=> $address->getRegionId(),
			'postcode'		=> $address->getPostcode(),
			'city'			=> $address->getCity(),
			'country_id'	=> $address->getCountryId(),
		];
		return $result;
	}

	protected function _getTotals() {
		$quote = $this->_getQuote();
		$subtotal = $quote->getSubtotalWithDiscount();
		$grandTotal = $quote->getGrandTotal();
		$shippingAmount = $quote->getShippingAddress()->getShippingAmount();
		$discountAmount = $grandTotal - $subtotal;
		$totalWithDiscount = $grandTotal - $discountAmount;
		return [
			'subtotal'		=> $subtotal,
			'discount'		=> $discountAmount,
			'shipping'		=> $shippingAmount,
			'total'			=> $totalWithDiscount
		];
	}

	protected function _getItems() {
		$items = $this->_getQuote()->getAllVisibleItems();
		$result = [];
		foreach ($items as $item) {
			$result[] = (object)[
				'item_id'				=> $item->getId(),
				'product'				=> (object)[
						'product_id'	=> $item->getProduct()->getId(),
						'sku'			=> $item->getProduct()->getSku(),
						'name'			=> $item->getProduct()->getName(),
						'url'			=> $item->getProduct()->getUrlPath(),
						'small_image'	=> $item->getProduct()->getSmallImage(),
						'thumbnail'		=> $item->getProduct()->getThumbnail()
				],
				'price'					=> $item->getPrice(),
				'discount_amount'		=> $item->getDiscountAmount(),
				'qty'					=> $item->getQty(),
				'total'					=> ($item->getRowTotal() - $item->getDiscountAmount()),
			];
		}
		return $result;
	}

	protected function _getShipping() {
		$address = $this->_getQuote()->getShippingAddress();
		$result = [
			'method'		=> $address->getShippingMethod(),
			'description'	=> $address->getShippingDescription()
		];
		return $result;
	}

	public function setAddress($type, $addressID){
		$quote = $this->_getQuote();
		if ($type == 'billing') {
			$quote->getBillingAddress()->setData($this->_parseAddress($addressID));
		} else {
			$quote->getShippingAddress()->setData($this->_parseAddress($addressID));
		}
		$quote->collectTotals()->save();
	}

	protected function _parseAddress($addressID) {
		// TODO: Recuperar o endereço e retornar os dados
	}

}