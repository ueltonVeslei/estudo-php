<?php
/**
 * Hook into Cart add product
 * Auto add products listed under the auto_add_product_ids attribute
 * 
 * The auto_add_product_ids attribute is an attribute part of every product that
 * can contain a comma separated list of product ids to be added to the cart automatically when 
 * when the product is added to the cart
 * 
 * @copyright 2009 Iceberg Commerce
 */

/**
 * Observer model
 */
class IcebergCommerce_AutoAddCart_Model_Observer
{	
	/**
	 * Hook into Checkout Cart Model when a product is added
	 * Auto add products in the auto_add_product_ids attribute
	 * 
	 * @param object $observer
	 */
	public function checkout_cart_product_add_after($observer)
	{
		$product = $observer->getEvent()->getProduct();
		
		$autoAddProductIdsArr = $this->getProductAutoAddProductIds($product);
		
		if (!empty($autoAddProductIdsArr))
		{
			// Add products to Cart
			Mage::getSingleton('checkout/cart')->addProductsByIds($autoAddProductIdsArr);
		}
	}
	
	
	/**
	 * Hook into Chackout Cart Model when a cart quantities are updated
	 * Remember what the orginal qty is so that we can use it after
	 * 
	 * @param object $observer
	 */
	public function checkout_cart_update_items_before($observer)
	{
		$cart     = $observer->getEvent()->getCart();
		$postData = $observer->getEvent()->getInfo();

		// Loop through post data
		foreach ($postData as $itemId => $itemInfo) 
		{
			$item = $cart->getQuote()->getItemById($itemId);
			/** CORREÇÃO DE ERRO PEGO PELO NEWRELIC **/
			if($item) {
				$item->setOriginalQty($item->getQty());
			}
		}
	}
	
	
	/**
	 * Hook into Chackout Cart Model when a cart quantities are updated
	 * Update qty of auto added products so they are the same as their parent product
	 * 
	 * @param object $observer
	 */
	public function checkout_cart_update_items_after($observer)
	{
		$cart     = $observer->getEvent()->getCart();
		$postData = $observer->getEvent()->getInfo();

		// Loop through post data
		foreach ($postData as $itemId => $itemInfo) 
		{
			$quantityChangeAmount = 0;
			
			// ----------------------------------------------------
			// Find how much of the qty has increased or decreased
			// ----------------------------------------------------
			$item = $cart->getQuote()->getItemById($itemId);
			
			if (!$item) 
			{
				continue;
			}
			
			if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) 
			{
				$quantityChangeAmount = $item->getOriginalQty() * -1; // Quantity all removed
			}
			
			$qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
			
			if ($qty > 0) 
			{
				$quantityChangeAmount = $qty - $item->getOriginalQty(); 
			}
			
			// --------------------------------------------------------
			// Change qty of "child items" the same amount as "parent"
			// --------------------------------------------------------
			if ($quantityChangeAmount != 0)
			{
				$product = Mage::getModel('catalog/product')
					->setStoreId(Mage::app()->getStore()->getId())
					->load($item->getProductId());
					
				$autoAddProductIdsArr = $this->getProductAutoAddProductIds($product);
				$this->updateChildCartItemQuantities($autoAddProductIdsArr, $quantityChangeAmount);
				
				$x[] = array($itemId,$quantityChangeAmount,$autoAddProductIdsArr);
			}
		}
	}
	
	
	/**
	 * Hook into Sales Quote Model when a product is removed from the cart
	 * Remove auto added products if the product(s) being removed have auto add products set
	 * 
	 * @param object $observer
	 */
	public function sales_quote_remove_item($observer)
	{
		$item = $observer->getEvent()->getQuoteItem();
		
		
		$product = Mage::getModel('catalog/product')
			->setStoreId(Mage::app()->getStore()->getId())
			->load($item->getProductId());
			
		$autoAddProductIdsArr = $this->getProductAutoAddProductIds($product);
		
		$quantityChangeAmount = $item->getQty() * -1;
		
		$this->updateChildCartItemQuantities($autoAddProductIdsArr, $quantityChangeAmount);
	}
	
	
	/**
	 * Update "Child" Cart items quantities after "parent" item qty updated
	 *
	 * @param array $autoAddProductIdsArr
	 * @param int $quantityChangeAmount
	 */
	private function updateChildCartItemQuantities($autoAddProductIdsArr, $quantityChangeAmount)
	{
		if ($quantityChangeAmount==0)
		{
			return;
		}
		
		if (!empty($autoAddProductIdsArr))
		{
			// Remove products from Cart or update qty
			foreach (Mage::getSingleton('checkout/cart')->getQuote()->getAllItems() as $i)
			{
				if (in_array($i->getProductId(),$autoAddProductIdsArr))
				{
					// See if we have to remove product or simply update qty
					$originalQty = $i->getOriginalQty() ? $i->getOriginalQty() : $i->getQty(); 
					$qtyUpdated = $originalQty + $quantityChangeAmount;

					if ($qtyUpdated <= 0)
					{
						Mage::getSingleton('checkout/cart')->getQuote()->removeItem($i->getId());
					}
					else 
					{
						$i->setQty($qtyUpdated);
					}
				}
			}
		}
		
		return true;
	}
	
	
	/**
	 * Return an array of auto add product ids for a product
	 * the array returned is already cleaned with
	 * @param object $product
	 * @return array
	 */
	private function getProductAutoAddProductIds($product)
	{
		$autoAddProductIds = trim($product->getAutoAddProductIds());
		if ($autoAddProductIds == '')
		{
			return array();
		}
		
		$autoAddProductIdsArr = explode(',',$autoAddProductIds);
		if (!is_array($autoAddProductIdsArr))
		{
			return array();
		}
		
		// ----------------------------------------------------------
		// Validate and clean up comma separated list of ids
		// ----------------------------------------------------------
		foreach ($autoAddProductIdsArr as $k=>$aaid)
		{
			$autoAddProductIdsArr[$k] = $aaid = (int) trim($aaid);
			
			// Check if valid id
			if (!($aaid > 0 && is_integer($aaid)))
			{
				unset($autoAddProductIdsArr[$k]);
				continue;
			}
			
			// Check if product with given id exists and can be shown
			$product = Mage::getModel('catalog/product')
				->setStoreId(Mage::app()->getStore()->getId())
				->load($aaid);
			
			if (!Mage::helper('catalog/product')->canShow($product))
			{
				unset($autoAddProductIdsArr[$k]);
				continue;
			}
		}
		
		$autoAddProductIdsArr = array_values(array_unique($autoAddProductIdsArr));
		
		return $autoAddProductIdsArr;
	}
	
}
