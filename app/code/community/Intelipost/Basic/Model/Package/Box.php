<?php

class Intelipost_Basic_Model_Package_Box
extends Varien_Object
{
	//protected $_simpleProducts;
	protected $_boxWidth;
	protected $_boxHeight;
	protected $_boxLength;
	protected $_boxAverageWeigth;
	protected $_boxVolume;
	private $_dimensions;
	protected $_calcMode;
	protected $_quoteMethod;

	public function _construct ()
	{	
		$this->_initBoxDefaults();

		return $this;
	}

	public function setDimension($dimension)
	{
		$this->_dimensions = $dimension;
	}

	public function getCalcMode()
	{
		if ($this->_calcMode == null)
		{
			$this->_calcMode = Mage::getStoreConfig ('intelipost_basic/quote_volume/advanced_vol_calc');
		}

		return $this->_calcMode;
	}	

	public function getQuoteMethod()
	{
		if ($this->_quoteMethod == null)
		{
			$this->_quoteMethod = Mage::getStoreConfig ('intelipost_basic/settings/quote_method');
		}

		return $this->_quoteMethod;
	}

	protected function _initBoxDefaults()
	{
		$this->_boxWidth = (int) Mage::getStoreConfig ('intelipost_basic/quote_volume/default_width');
    	$this->_boxHeight = (int) Mage::getStoreConfig ('intelipost_basic/quote_volume/default_height');
    	$this->_boxLength = (int) Mage::getStoreConfig ('intelipost_basic/quote_volume/default_length');
    	$this->_boxAverageWeigth = (float) Mage::getStoreConfig('intelipost_basic/quote_volume/default_weight');

    	$this->_boxVolume = $this->_boxWidth * $this->_boxHeight * $this->_boxLength;

    	return $this;
	}

	protected function getCalcModeValue()
	{
		if (Mage::getStoreConfig ('intelipost_basic/quote_volume/advanced_vol_calc') == 'dimensions') {
			return $this->_boxVolume;
		}
		else {
			return $this->_boxAverageWeigth;
		}

	}

	private function getCalcValue($totalVolume, $totalWeigth)
	{
		if (Mage::getStoreConfig ('intelipost_basic/quote_volume/advanced_vol_calc') == 'dimensions') {
			return $totalVolume;
		}
		else {
			return $totalWeigth;
		}
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
	
	public function calcBox($simpleProducts)
	{
    	$simpleProductsQty = count ($simpleProducts);

    	$packageWidth  = 0;
    	$packageHeight = 0;
    	$packageLength = 0;
    	$packagePrice  = 0;
    	$packageWeight = 0;
    	$packageVolume = 0;
    	$packageQty = 0;
    	//$packageItens = array();
    	$productsIndex = array();
    	$j = 0;

    	for ($i = 0; $i < $simpleProductsQty; $i ++)
    	{
	        $product = $simpleProducts[$i];

	        if (Mage::getStoreConfig('intelipost_basic/product_attributes/use_volume_attr'))
	        {
	        	$productVolume = $product->getData(Mage::getStoreConfig('intelipost_basic/product_attributes/volume_attr'));
	        	$productVolume = $productVolume ? $productVolume : Mage::getStoreConfig('intelipost_basic/product_attributes/volume_contingency');
	        	$cubic = $this->_dimensions->_getCubic ($productVolume);
	        	$productWidth = $cubic;
	        	$productHeight = $cubic;
	        	$productLength = $cubic;
	        	$productPrice  = $this->getSpecialPrice($product->getSpecialPrice(), $product->getSpecialFromDate(), $product->getSpecialToDate()) ? $product->getSpecialPrice() : $product->getPrice();
		        $productWeight = $this->_dimensions->_getCustomWeight($product->getWeight());
	        }
	        else
	        {
		        $productWidth       = $product->getData ($this->_dimensions->_widthAttribute)  && $product->getData ($this->_dimensions->_widthAttribute) >=  $this->_dimensions->_productWidth ? $product->getData ($this->_dimensions->_widthAttribute)  : $this->_dimensions->_productWidth;
		        $productHeight      = $product->getData ($this->_dimensions->_heightAttribute) && $product->getData ($this->_dimensions->_heightAttribute) >=  $this->_dimensions->_productHeight ? $product->getData ($this->_dimensions->_heightAttribute) : $this->_dimensions->_productHeight;
		        $productLength      = $product->getData ($this->_dimensions->_lengthAttribute) && $product->getData ($this->_dimensions->_lengthAttribute) >=  $this->_dimensions->_productLength ? $product->getData ($this->_dimensions->_lengthAttribute) : $this->_dimensions->_productLength;
		        $productPrice  = $this->getSpecialPrice($product->getSpecialPrice(), $product->getSpecialFromDate(), $product->getSpecialToDate()) ? $product->getSpecialPrice() : $product->getPrice();
		        $productWeight = $this->_dimensions->_getCustomWeight($product->getWeight());
		        $productVolume = $this->_dimensions->_getVolume ('product', $product);
	    	}

	        if (!$productPrice) $productPrice = $product->getVlr();
	        
	        if ($this->getCalcMode() == 'unitary' || $this->getQuoteMethod() == 'product')
	        {
	        	$productsIndex = array_merge($productsIndex, array('product'.$i => array('index' => $i)));
	        	$packageWidth  += $productWidth;
	            $packageHeight += $productHeight;
	            $packageLength += $productLength;
	            $packagePrice  += $productPrice;
	            $packageWeight += $productWeight;
	            $packageVolume += $productVolume;
	            $packageQty = 1;

	            if (Mage::getStoreConfig('intelipost_basic/settings/volume_unity') == 'mt')
			    {
			        $packageVolume *= 1000000;
			    }
	            //$cubic = $this->_dimensions->_getCubic ($packageVolume);
	        	$data = array( 'packageData' => array(
	                    'width'  => $productWidth,
	                    'height' => $productHeight,
	                    'length' => $productLength,
	                    'price'  => $packagePrice,
	                    'weight' => $packageWeight,
	                    'volume' => $packageVolume,
	                    'qty'    => $packageQty,                    
	                ), 'index' => $productsIndex);


	        	return $data;	        	
	        }

	        if ($this->getCalcValue($productVolume + $packageVolume, $productWeight + $packageWeight) <= $this->getCalcModeValue())
	        {
	            //$packageItens =  array_merge($packageItens, array('product'.$i => array('weight' => $product->getWeight())));
	            $productsIndex = array_merge($productsIndex, array('product'.$i => array('index' => $i)));
	            $packageWidth  += $productWidth;
	            $packageHeight += $productHeight;
	            $packageLength += $productLength;
	            $packagePrice  += $productPrice;
	            $packageWeight += $productWeight;
	            $packageVolume += $productVolume;
	            $packageQty ++;           
	        }
	        else
	        {
	            if ($packageQty == 0)
	            {
	                //$packageItens =  array_merge($packageItens, array('product'.$i => array('weight' => $product->getWeight())));
	                $productsIndex = array_merge($productsIndex, array('product'.$i => array('index' => $i)));
	                $packageWidth  += $productWidth;
	                $packageHeight += $productHeight;
	                $packageLength += $productLength;
	                $packagePrice  += $productPrice;
	                $packageWeight += $productWeight;
	                $packageVolume += $productVolume;
	                $packageQty = 1;	                

	                if (Mage::getStoreConfig('intelipost_basic/settings/volume_unity') == 'mt')
				    {
				        $packageVolume *= 1000000;
				    }

	                $cubic = $this->_dimensions->_getCubic ($packageVolume);

	                $data = array( 'packageData' => array(
	                    'width'  => $cubic,
	                    'height' => $cubic,
	                    'length' => $cubic,
	                    'price'  => $packagePrice,
	                    'weight' => $packageWeight,
	                    'volume' => $packageVolume,
	                    'qty'    => $packageQty,                    
	                ), 'index' => $productsIndex);

	               // Mage::log($data);
	                return $data;
	            }
        	}
    	}

    	if (Mage::getStoreConfig('intelipost_basic/settings/volume_unity') == 'mt')
	    {
	        $packageVolume *= 1000000;
	    }

	    $cubic = $this->_dimensions->_getCubic ($packageVolume);

	    $data = array( 'packageData' => array(
	        'width'  => $cubic,
	        'height' => $cubic,
	        'length' => $cubic,
	        'price'  => $packagePrice,
	        'weight' => $packageWeight,
	        'volume' => $packageVolume,
	        'qty'    => $packageQty,
	        ), 'index' => $productsIndex);

	    return $data;
    }
}