<?php

class Intelipost_Quote_Model_Quote_Product
{
	private $_packages = array ();
	protected $_simpleProducts = array ();
	private $_dimensions;
	public $_bundleProduct;
	protected $_productsQty = array();
	public $_bundleOptions = array();

	public function fetchProductQuote($items, $dimensions)
	{
		$this->_dimensions = $dimensions;
		$this->getSimpleProducts($items); 
		$productsCount = count ($this->_simpleProducts);

		$j = 0;
		for ($i = 0; $i < $productsCount; $i ++)
		{
			$product = $this->_simpleProducts[$i];

			if (Mage::getStoreConfig('intelipost_basic/product_attributes/use_volume_attr'))
	        {	        				  
	        	$productVolume = $product->getData(Mage::getStoreConfig('intelipost_basic/product_attributes/volume_attr'));
	        	$productVolume = $productVolume ? $productVolume : Mage::getStoreConfig('intelipost_basic/product_attributes/volume_contingency');

	        	if (Mage::getStoreConfig('intelipost_basic/settings/volume_unity') == 'mt')
			    {
			        $productVolume *= 1000000;
			    }

	        	$cubic = $this->_dimensions->_getCubic ($productVolume);
	        	$productWidth = $cubic;
	        	$productHeight = $cubic;
	        	$productLength = $cubic;	        	
	        }
	        else
	        {
		        $productWidth       = $product->getData ($this->_dimensions->_widthAttribute)  && $product->getData ($this->_dimensions->_widthAttribute) >  0 ? $product->getData ($this->_dimensions->_widthAttribute)  : $this->_dimensions->_productWidth;
		        $productHeight      = $product->getData ($this->_dimensions->_heightAttribute) && $product->getData ($this->_dimensions->_heightAttribute) > 0 ? $product->getData ($this->_dimensions->_heightAttribute) : $this->_dimensions->_productHeight;
		        $productLength      = $product->getData ($this->_dimensions->_lengthAttribute) && $product->getData ($this->_dimensions->_lengthAttribute) > 0 ? $product->getData ($this->_dimensions->_lengthAttribute) : $this->_dimensions->_productLength;
		    }

            $productPrice = $product->getFinalPrice();

		    if(!strcmp(Mage::getStoreConfig('carriers/intelipost/price_config'),'product'))
            {
            	if (isset($this->_simpleProducts[$i][$i]))
            	{
            		$productFinalPrice = $productPrice * ($this->_simpleProducts[$i][$i]/100);
            	}
            	else
            	{
		        	$productFinalPrice = $this->getSpecialPrice($product->getSpecialPrice(), $product->getSpecialFromDate(), $product->getSpecialToDate()) ? $product->getSpecialPrice() : $productPrice;
		    	}
            }
            else
            {
                $subtotalAmount = $this->_getSubtotalAmount ();
                $discountAmount = $this->_getDiscountAmount ();

                $productFinalPrice = (($productPrice / $subtotalAmount) * $discountAmount)+ $productPrice;
            }

		    $productWeight      = $this->_dimensions->_getCustomWeight($product->getWeight());
	        $productSku         = $product->getSku();
	        $productDescription = $product->getName();
	        
            $productId = $product->getId ();

	        $data = array( 'packageData' => array(
	                    'width'        => $productWidth,
	                    'height'       => $productHeight,
	                    'length'       => $productLength,
	                    'price'        => $productFinalPrice,
	                    'weight'       => $productWeight,
	                    'sku'          => $productSku,
                        'id'           => $productId,
	                    'description'  => $productDescription,
	                    'qty'          => $this->_productsQty[$i]              
	                ));

	        $this->_addToPackage ($data['packageData'], $j ++);
    	}
	}

	private function _addToPackage ($packageData, $index)
	{
	    $this->_packages[$index] = $packageData;
	}

	private function getSpecialPrice($specialPrice, $dateStart, $dateLimit)
	{
	    $return = false;
	    if (!$dateLimit && !$dateStart) {
	        return $return;
	    }
	    else
	    {
	    	if (!$dateLimit && $specialPrice) {
	    		return true;
	    	}

	        $time = strtotime($dateLimit);
	        $specialDate = date('Y-m-d',$time);
	        $actualDate = date('Y-m-d');
	        if ($specialDate >= $actualDate && $specialPrice) {
	            $return = true;
	        }
	    }

	    return $return;
	}
	
	private function getSimpleProducts($items)
	{
	    if ($this->_simpleProducts) {
	        return $this->_simpleProducts;
	    }

	    $j = 0;
	    foreach ($items as $child)
	    {
	        $product_id = $child->getProductId ();
	        $product = Mage::getModel ('catalog/product')->load ($product_id);
	        $type_id = $product->getTypeId ();
	        $belongbundle = false;

	        if (strcmp ($type_id, 'simple')) continue;
	        
	        $qty = $this->_getQty ($child);
	        $parentItem_id = 0;

	        $parentItem = $child->getParentItem ();
		    if (!empty($parentItem))
		    {
		        $parentItem_id = $parentItem->getId ();
		    }

		    $targetItem = $parentItem_id > 0 ? $parentItem : $child;

		    $productP = Mage::getModel('catalog/product')->load($targetItem->getProductId());
	    
	    	if ($productP->getTypeId() == 'bundle') 
	    	{
	    		$belongbundle = true;
	    	}


	        $product = Mage::getModel ('catalog/product')->load ($child->getProductId());
	        
	        $this->_simpleProducts [$j] = $product;
	        if ($belongbundle) 
	        {
	        	$promo = $this->getSpecialPrice($productP->getSpecialPrice(), $productP->getSpecialFromDate(), $productP->getSpecialToDate());
	        	if ($promo) {
	        		$this->_simpleProducts[$j][$j] = $productP->getSpecialPrice();
	        	}
	        }
	        $this->_productsQty [$j] = (int)$qty;	        
	        $j = $j + 1;
	    }

	    return $this;
	}

	public function getPackages()
	{
    	return $this->_packages;
	}
	
	/*
	private function _getQty ($item)
	{
	    $qty = 0;

	    $parentItem = $item->getParentItem ();
	    $targetItem = !empty ($parentItem) && $parentItem->getId () > 0 ? $parentItem : $item;

	    $product = Mage::getModel('catalog/product')->load($targetItem->getProductId());

	    if ($product->getTypeId() != 'simple' && $product->getWeight() > 0) {
	    	$targetItem = $parentItem->getId () > 0 ? $parentItem : $item;
	    }
	    else
	    {
	    	$targetItem = $item;
	    }

	    if ($targetItem instanceof Mage_Sales_Model_Quote_Item)
	    {
	        $qty = $targetItem->getQty ();
	    }
	    elseif ($targetItem instanceof Mage_Sales_Model_Order_Item)
	    {
	        $qty = $targetItem->getShipped () ? $targetItem->getShipped () : $targetItem->getQtyInvoiced ();
	        if ($qty == 0) {
	            $qty = $targetItem->getQtyOrdered();
	        }
	    }

	    return $qty;
	}*/

	public function getBundledQty($bundled_id, $simple_id)
	{
	    $return = 0;

	    //if (!$this->_bundleProduct) {
	        $this->_bundleProduct = Mage::getModel('catalog/product')->load($bundled_id);
	    //}

	    $selectionCollection = $this->_bundleProduct->getTypeInstance(true)->getSelectionsCollection(
	        $this->_bundleProduct->getTypeInstance(true)->getOptionsIds($this->_bundleProduct), $this->_bundleProduct
	    );
	    

	    foreach($selectionCollection as $option) 
	    {
	        if ($option->getId() == $simple_id && !in_array($option->getId(), $this->_bundleOptions)) {
	            array_push($this->_bundleOptions, $option->getId());
	            return $option->getSelectionQty();
	        }       
	    }
	    
	    return $return;
	}

	private function _getQty ($item)
	{
	    $qty = 0;
	    $parentItem_id = 0;

	    $parentItem = $item->getParentItem ();
	    if (!empty($parentItem))
	    {
	        $parentItem_id = $parentItem->getId ();
	    }

	    $targetItem = $parentItem_id > 0 ? $parentItem : $item;

	    $product = Mage::getModel('catalog/product')->load($targetItem->getProductId());
	    
	    if ($product->getTypeId() == 'bundle')
	    {
	        $simple_pdt = Mage::getModel('catalog/product')->load($item->getProductId());
	        $qty = $this->getBundledQty ($product->getId (), $simple_pdt->getId());

	        if ($targetItem instanceof Mage_Sales_Model_Quote_Item)
	        {
	            $qty *= $targetItem->getQty ();
	        }
	        elseif ($targetItem instanceof Mage_Sales_Model_Order_Item)
	        {
	            $target_qty = $targetItem->getShipped () ? $targetItem->getShipped () : $targetItem->getQtyInvoiced ();
	            if ($target_qty == 0) {
	                $target_qty = $targetItem->getQtyOrdered();
	            }
	            
	            $qty *= $target_qty;
	        }
	    }	   
	    elseif ($targetItem instanceof Mage_Sales_Model_Quote_Item)
	    {
	        $qty = $targetItem->getQty ();
	    }
	    elseif ($targetItem instanceof Mage_Sales_Model_Order_Item)
	    {
	        $qty = $targetItem->getShipped () ? $targetItem->getShipped () : $targetItem->getQtyInvoiced ();
	        if ($qty == 0) {
	            $qty = $targetItem->getQtyOrdered();
	        }
	    }

	    return $qty;
	}

    private function _getTotals ()
    {
        if (Mage::app()->getStore()->isAdmin())
        {
            $totals = Mage::getSingleton('adminhtml/session_quote')->getQuote ()->getTotals();
        }
        else
        {
            $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
        }

        return $totals;
    }

    private function _getDiscountAmount ()
    {
        $totals = $this->_getTotals ();

        $result = array_key_exists('discount', $totals) ? $totals['discount']->getValue() : 0;

        return $result < 0 ? $result : 0;
    }

    private function _getSubtotalAmount ()
    {
        $totals = $this->_getTotals ();

        $result = array_key_exists('subtotal', $totals) ? $totals['subtotal']->getValue() : 0;

        return $result > 0 ? $result : 0;
    }

}

