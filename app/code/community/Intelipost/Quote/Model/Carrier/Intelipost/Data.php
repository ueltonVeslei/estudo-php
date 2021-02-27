<?php

class Intelipost_Quote_Model_Carrier_Intelipost_Data
//extends Varien_Object
{

protected $_originZipCode;
protected $_destZipCode;
protected $_packageWeight;
protected $_packagePrice;
protected $_dimension;
protected $_prazoProduto;
protected $_weight_unit;
protected $_intelipostValorEmbutido;
protected $_intelipostPrazoProdutos;
protected $_quoteProduct;

public function checkZipCodeOrigin($dest_postcode = null)
{
    if (Mage::getStoreConfig('intelipost_basic/settings/use_another_origin'))
    {
        $dest_postcode = (int)$this->removePostcodeFormat($dest_postcode);
        $min_range = (int)$this->removePostcodeFormat(Mage::getStoreConfig('intelipost_basic/settings/destination_range_start'));
        $max_range = (int)$this->removePostcodeFormat(Mage::getStoreConfig('intelipost_basic/settings/destination_range_end'));

        $validNewOrigin =  filter_var(
                                        $dest_postcode, 
                                        FILTER_VALIDATE_INT, 
                                        array(
                                            'options' => array(
                                                'min_range' => $min_range, 
                                                'max_range' => $max_range
                                            )
                                        )
                                    );
        if ($validNewOrigin)
        {
            $this->_originZipCode = $this->removePostcodeFormat(Mage::getStoreConfig('intelipost_basic/settings/aux_origin_zipcode'));
            return true;
        }
        else
        {
            if (strlen($min_range) != strlen($max_range))
            {
                if (strlen($min_range) == strlen($dest_postcode) && $dest_postcode >= $min_range)
                {
                    $this->_originZipCode = $this->removePostcodeFormat(Mage::getStoreConfig('intelipost_basic/settings/aux_origin_zipcode'));
                    return true;
                }
                else if ($dest_postcode <= $max_range)
                {   
                    $this->_originZipCode = $this->removePostcodeFormat(Mage::getStoreConfig('intelipost_basic/settings/aux_origin_zipcode'));
                    return true;
                }
            }
        }
    }
	$this->_originZipCode = Mage::getStoreConfig ('intelipost_basic/settings/zipcode');
	$this->_originZipCode = $this->_originZipCode ? $this->_originZipCode : Mage::helper('quote')->getOriginZipCode();
	//if (!$this->isValidPostcode($this->_originZipCode)) return;

	$this->_originZipCode = $this->removePostcodeFormat($this->_originZipCode);

	return true;
}

public function checkZipCodeDest($postcode)
{
	if (!$this->isValidPostcode($postcode)) return;

	$this->_destZipCode = $this->removePostcodeFormat($postcode);

    if ($this->_destZipCode == '00000000') return;
    
	return true;
}

public function isValidPostcode($postcode)
{
    if(!$postcode = $this->removePostcodeFormat($postcode)) return;
    
    if(!preg_match('/^[0-9]{8}$/i', $postcode)) return;
    
    return true;
}

public function setQuoteProduct($items)
{
    $this->_quoteProduct = Mage::getModel('quote/quote_product');
    $this->_quoteProduct->fetchProductQuote($items, $this->getDimension());
}

public function getQuoteProduct()
{
    return $this->_quoteProduct;
}

public function getDimension()
{
	if ($this->_dimension == null)
    {
		$this->_dimension = Mage::getModel('basic/package_dimension');
    }

	return $this->_dimension;
}

public function setPackageWeight($packageWeight, $items)
{
    $_weight_unit = Mage::getStoreConfig('carriers/intelipost/weight_unit');

    if ($packageWeight == 0)
    {
        $packageWeight = $this->getDimension()->_getCustomWeight($packageWeight, $items );
        //return $this;
    }

    if($_weight_unit == 'gr') 
	{
        $this->_packageWeight = number_format($packageWeight/1000, 2, '.', '');
	}
    else 
    {
        $this->_packageWeight = number_format($packageWeight, 2, '.', '');
    }
    
    return $this;
}

public function setPackagePrice($packagePrice)
{
	$this->_packagePrice = $packagePrice;
    
    return $this;
}

public function getOriginZipCode()
{
	return $this->_originZipCode;
}

public function getDestZipCode()
{
	return $this->_destZipCode;
}

public function getPackageWeight()
{
	return $this->_packageWeight;
}

public function getPackagePrice()
{
	return $this->_packagePrice;
}

public function removePostcodeFormat($postcode)
{
    return str_replace('-', null, trim($postcode));
}
/*
public function getProdutosValorEmbutido($items, $freeShipping)
{
    $this->_intelipostValorEmbutido = 0;
    $bundles = array();
    foreach ($items as $child)
    {
        $parentItem = $child->getParentItem ();
        $targetItem = !empty ($parentItem) && $parentItem->getId () > 0 ? $parentItem : $child;

        $product = Mage::getModel('catalog/product')->load($targetItem->getProduct()->getId());
        
        if ($product->getTypeId() == 'bundle')
        {
            if (!in_array($product->getId(), $bundles)) 
            {           
                array_push($bundles, $product->getId());
                $bundle_valor_embutido = $this->getBundleValorEmbutido($product, $freeShipping);
                    
                if ($bundle_valor_embutido) {
                    $this->_intelipostValorEmbutido += $bundle_valor_embutido;
                }
                else {
                    $this->_intelipostValorEmbutido += $this->getBundledProductsValorEmbutido($product, $freeShipping);
                }               
            }
            continue;
        }
        
        if ($freeShipping) {
        $attribute = $bundle_product->getResource()->getAttribute('frete_price');
        }
        else {
            $attribute = $bundle_product->getResource()->getAttribute('intelipost_frete_nofg');
        }

        if ($attribute)
        {
            $this->_intelipostValorEmbutido += $attribute ->getFrontend()->getValue($product) * $targetItem->getQty();  
        }
        
    }

    return $this->_intelipostValorEmbutido;
}

public function getBundleValorEmbutido($bundle_product, $freeShipping)
{
    $return = 0;    

    if ($freeShipping) {
        $attribute = $bundle_product->getResource()->getAttribute('frete_price');
    }
    else {
        $attribute = $bundle_product->getResource()->getAttribute('intelipost_frete_nofg');
    }
    
    if ($attribute) {       
        $return = $attribute ->getFrontend()->getValue($bundle_product);
    }
    
    return $return;
}

public function getBundledProductsValorEmbutido($bundled_product, $freeShipping)
{
    
    $return = 0;
    
    $selectionCollection = $bundled_product->getTypeInstance(true)->getSelectionsCollection(
        $bundled_product->getTypeInstance(true)->getOptionsIds($bundled_product), $bundled_product
    );
 
    $bundled_items = array();
    foreach($selectionCollection as $option) 
    {
        $option_qty = $option->getSelectionQty();
        $product = Mage::getModel('catalog/product')->load($option->getId());        

        if ($freeShipping) {
        $attribute = $bundle_product->getResource()->getAttribute('frete_price');
        }
        else {
            $attribute = $bundle_product->getResource()->getAttribute('intelipost_frete_nofg');
        }

        if ($attribute)
        {
            $return += $attribute ->getFrontend()->getValue($product) * $option_qty;  
        }
    }
    
    return $return;
}*/
public function calcPrazoProdutos($items)
{
    $this->_intelipostPrazoProdutos = 0;
    foreach ($items as $child)
    {
        $parentItem = $child->getParentItem ();
        $targetItem = !empty ($parentItem) && $parentItem->getId () > 0 ? $parentItem : $child;

        $product = Mage::getModel('catalog/product')->load($targetItem->getProduct()->getId());
        $atributoProduto = Mage::helper('quote')->getConfigData('prazo_produto_att');
        $attribute = $product->getResource()->getAttribute($atributoProduto);
        //Mage::log($attribute);
        if ($attribute)
        {
            $prazo = $this->formatPrazoProduto($attribute ->getFrontend()->getValue($product));
            $prazo = empty($prazo) ? 0 : $prazo;            
            $this->_intelipostPrazoProdutos = $this->_intelipostPrazoProdutos > $prazo ? $this->_intelipostPrazoProdutos : $prazo;  
        }
        
    }
    
    return $this;
}

public function getPrazoProdutos()
{
    return $this->_intelipostPrazoProdutos;
}

public function hasPrazoProdutos()
{
    $return = 0;

    if ($this->_intelipostPrazoProdutos >= 0) {
        $return = 1;
    }

    return $return;
}

public function setPrazoProdutos($items)
{
    $this->calcPrazoProdutos($items);

    return $this;
}
public function formatPrazoProduto($prazo)
{
    if (is_string($prazo))
    {
        preg_match_all('!\d+!', $prazo, $matches);

        foreach ($matches as $key => $value) 
        {
            $prazo = (int)$value[0];
        }
    }

    return $prazo;
}


}

