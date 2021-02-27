<?php

class Intelipost_Basic_Model_Package_Dimension
extends Varien_Object
{

const PRODUCT_DEFAULT_WIDTH = 11;
const PRODUCT_DEFAULT_HEIGHT = 2;
const PRODUCT_DEFAULT_LENGTH = 16;

private $_packages = array ();
protected $_simpleProducts = array ();
public $_bundleProduct;
private $_box;
public $_bundleOptions = array();

public function _construct ()
{
    $this->_widthAttribute = Mage::getStoreConfig ('intelipost_basic/product_attributes/width');
    $this->_heightAttribute = Mage::getStoreConfig ('intelipost_basic/product_attributes/height');
    $this->_lengthAttribute = Mage::getStoreConfig ('intelipost_basic/product_attributes/length');

    $this->_productWidth = (int) Mage::getStoreConfig ('intelipost_basic/product_attributes/default_width');
    $this->_productHeight = (int) Mage::getStoreConfig ('intelipost_basic/product_attributes/default_height');
    $this->_productLength = (int) Mage::getStoreConfig ('intelipost_basic/product_attributes/default_length');

    $this->_averageWeigth = (float) Mage::getStoreConfig ('intelipost_basic/product_attributes/average_weight');

    if (!$this->_productWidth) $this->_productWidth = self::PRODUCT_DEFAULT_WIDTH;
    if (!$this->_productHeight) $this->_productHeight = self::PRODUCT_DEFAULT_HEIGHT;
    if (!$this->_productLength) $this->_productLength = self::PRODUCT_DEFAULT_LENGTH;

    //$this->_boxWidth = (int) Mage::getStoreConfig ('intelipost_basic/box/default_width');
    //$this->_boxHeight = (int) Mage::getStoreConfig ('intelipost_basic/box/default_height');
    //$this->_boxLength = (int) Mage::getStoreConfig ('intelipost_basic/box/default_length');
    //$this->_boxAverageWeigth = (float) Mage::getStoreConfig('intelipost_basic/box/default_weight');

    $this->_weightUnit = Mage::getStoreConfig ('intelipost_basic/settings/weight_unit');
}

public function calcItemsDimension($items)
{
    if (count($items))
    {
        $calcMode       = Mage::getStoreConfig ('intelipost_basic/settings/quote_method');
        $calcDimensions = Mage::getStoreConfig ('intelipost_basic/quote_volume/advanced_vol_calc');
        Mage::log($calcDimensions);
        if ($calcDimensions != 'no' || $calcMode == 'product')
        {
            $this->_calcAdvancedDimensions ($items);
        }
        else
        {
            $this->_calcSimpleDimensions ($items);
        }        
    }
    else
    {
        throw new Mage_Shipping_Exception($this->_getHelper()->__('Cart is empty.'));
    }

    return $this;
}

private function _hasProductToInsert()
{
    return (count($this->_simpleProducts) > 0);
}

private function _removeSimpleProduct($index)
{
    foreach ($index as $key => $value)
    {
        $indexVal = $value['index'];
        unset($this->_simpleProducts[$indexVal]);        
    }

    $this->_simpleProducts = array_values($this->_simpleProducts);

    return $this;    
}

private function _calcAdvancedDimensions ($items)
{   
    $this->getSimpleProducts($items); 
    $this->_box = Mage::getModel('basic/package_box');
    $this->_box->setDimension($this);

    $j = 0;

    while ($this->_hasProductToInsert())
    {
        $data = $this->_box->calcBox($this->_simpleProducts);
        $this->_addToPackage ($data['packageData'], $j ++);
        $this->_removeSimpleProduct($data['index']);
    }
    
    return $this;
}

private function getSpecialPrice($dateBegin, $dateLimit)
{
        
    if ($dateBegin && !$dateLimit) return true;
        
    $return = false;
    if (!$dateLimit) {
        return $return;
    }
    else
    {
        $time = strtotime($dateLimit);
        $specialDate = date('Y-m-d',$time);
        $actualDate = date('Y-m-d');
        if ($specialDate >= $actualDate) {
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
    $bundles = array();
    $j = 0;
    foreach ($items as $child)
    {
        $parentItem = $child->getParentItem ();
        $targetItem = !empty ($parentItem) && $parentItem->getId () > 0 ? $parentItem : $child;

        $this->_bundleProduct = Mage::getModel('catalog/product')->load($targetItem->getProductId());
        Mage::log($this->_bundleProduct->getTypeId());
        if ($this->_bundleProduct->getTypeId() == 'bundle')
        {           
            if (!in_array($this->_bundleProduct->getId(), $bundles)) 
            {
                if ($this->_bundleProduct->getWeight() > 0)
                {
                    array_push($bundles, $this->_bundleProduct->getId());
                    $this->_bundleProduct->setVlr($this->getBundledValues());

                    if ($targetItem instanceof Mage_Sales_Model_Quote_Item)
                    {
                        $qty = $targetItem->getQty ();
                    }
                    elseif ($targetItem instanceof Mage_Sales_Model_Order_Item)
                    {
                        $target_qty = $targetItem->getShipped () ? $targetItem->getShipped () : $targetItem->getQtyInvoiced ();
                        if ($target_qty == 0) {
                            $target_qty = $targetItem->getQtyOrdered();
                        }
                            
                        $qty = $target_qty;
                    }

                    for ($i = 0; $i < $qty; $i ++)
                    {
                       $this->_simpleProducts [$j ++] = $this->_bundleProduct;
                    }                    
                }
            }
            
            if ($this->_bundleProduct->getWeight() > 0) {
                continue;
            }
        }
        
        $product_id = $child->getProductId ();
        $product = Mage::getModel ('catalog/product')->load ($product_id);
        $type_id = $product->getTypeId ();
                
        if (strcmp ($type_id, 'simple')) continue;
        
        $qty = $this->_getQty ($child);

        $product = Mage::getModel ('catalog/product')->load ($child->getProductId());

        for ($i = 0; $i < $qty; $i ++)
        {
            $this->_simpleProducts [$j ++] = $product;
        }
    }

    return $this;
}

public function getBundledValues()
{
    $return = 0;
    
    $selectionCollection = $this->_bundleProduct->getTypeInstance(true)->getSelectionsCollection(
        $this->_bundleProduct->getTypeInstance(true)->getOptionsIds($this->_bundleProduct), $this->_bundleProduct
    );
 
    foreach($selectionCollection as $option) 
    {
        $option_qty = $option->getSelectionQty();
        $product = Mage::getModel('catalog/product')->load($option->getId());
        $return += $this->getSpecialPrice($product->getSpecialFromDate(), $product->getSpecialToDate()) ? $product->getSpecialPrice() * $option_qty : $product->getPrice() * $option_qty;        
    }
        
    
    return $return;
}

private function _calcSimpleDimensions($items)
{
    $packageWidth  = 0;
    $packageHeight = 0;
    $packageLength = 0;
    $packagePrice  = 0;
    $packageWeight = 0;
    $packageQty    = 0;
    $packageVolume = 0;
    $productVolume = 0;

    foreach ($items as $child)
    {
        $product_id = $child->getProductId();
        $product = Mage::getModel ('catalog/product')->load ($product_id);

        $type_id = $product->getTypeId ();
        if (strcmp ($type_id, 'simple')) continue;

        $qty = $this->_getQty ($child);

        $product = Mage::getModel ('catalog/product')->load ($child->getProductId());

        $packageWidth  += $product->getData ($this->_widthAttribute) ? $product->getData ($this->_widthAttribute)   : $this->_productWidth;
        $packageHeight += $product->getData ($this->_heightAttribute) ? $product->getData ($this->_heightAttribute) : $this->_heightAttribute;
        $packageLength += $product->getData ($this->_lengthAttribute) ? $product->getData ($this->_lengthAttribute) : $this->_lengthAttribute;
        $packagePrice  += $this->getSpecialPrice($product->getSpecialFromDate(), $product->getSpecialToDate()) ? $product->getSpecialPrice() : $product->getPrice();
        $packageWeight += ($this->_getCustomWeight($product->getWeight()) * $qty);
        $packageQty    += $qty;
        $productVolume += ($this->_getVolume ('product', $product) * $qty);
    }
    
    $packageVolume += $productVolume;

    if (Mage::getStoreConfig('intelipost_basic/settings/volume_unity') == 'mt')
    {
        $packageVolume *= 1000000;
    }
    
    $cubic = pow ($packageVolume, 1/3);
    $cubic = round ($cubic,1);

    $this->setHeight($cubic)
        ->setWidth($cubic)
        ->setLength($cubic)
        ->setWeigth($packageWeight);

    $packageData = array(
        'width'  => $cubic,
        'height' => $cubic,
        'length' => $cubic,
        'price'  => $packagePrice,
        'weight' => $packageWeight,
        'volume' => $packageVolume,
        'qty'    => $packageQty,
    );


    $this->_addToPackage ($packageData, 1);

    return $this;
}

public function _getVolume ($type = 'product', $object)
{
    $width = $object->getData ($this->_widthAttribute);
    $height = $object->getData ($this->_heightAttribute);
    $length = $object->getData ($this->_lengthAttribute);

    $defaultWidth = !strcmp ($type, 'product') ? $this->_productWidth : $this->_boxWidth;
    $defaultHeight = !strcmp ($type, 'product') ? $this->_productHeight : $this->_boxHeight;
    $defaultLength = !strcmp ($type, 'product') ? $this->_productLength : $this->_boxLength;

    $width  = ($width ? $width : $defaultWidth);
    $height = ($height ? $height : $defaultHeight);
    $length = ($length ? $length : $defaultLength);

    $result = $width * $height * $length;

    return $result;
}

public function _getCubic ($volume)
{
    $cubic = pow ($volume, 1/3);
    $result = round ($cubic, 1);

    return $result;
}

public function getBundledQty($bundled_id, $simple_id)
{
    $return = 0;
    
    if (!$this->_bundleProduct) {
        $this->_bundleProduct = Mage::getModel('catalog/product')->load($bundled_id);
    }

    $selectionCollection = $this->_bundleProduct->getTypeInstance(true)->getSelectionsCollection(
        $this->_bundleProduct->getTypeInstance(true)->getOptionsIds($this->_bundleProduct), $this->_bundleProduct
    );
    
    foreach($selectionCollection as $option) 
    {
        if ($option->getId() == $simple_id && !in_array($option->getId(), $this->_bundleOptions)) {
            array_push($this->_bundleOptions, $option->getId());
            $return += $option->getSelectionQty();
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
    
    if ($product->getTypeId() == 'bundle' && $product->getWeight() > 0)
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

private function _addToPackage ($packageData, $index)
{
    $this->_packages[$index] = $packageData;
}

public function getPackages()
{
    return $this->_packages;
}

public function isValid()
{
    try {
        if ($this->getWidth() > $this->_maxWidth)
        {
            throw new Mage_Shipping_Exception($this->_getHelper()->__('The width of the products is greater than %d cm', $this->_maxWidth));
        }

        if ($this->getHeight() > $this->_maxHeight)
        {
            throw new Mage_Shipping_Exception($this->_getHelper()->__('The heigth of the products is greater than %d cm', $this->_maxHeight));
        }

        if ($this->getLength() > $this->_maxLength)
        {
            throw new Mage_Shipping_Exception($this->_getHelper()->__('The length of the products is greater than %d cm', $this->_maxLength));
        }

        if (($this->getWidth() + $this->getHeigth() + $this->getLength()) > $this->_maxSum)
        {
            throw new Mage_Shipping_Exception($this->_getHelper()->__('The dimensions of the products have passed the limit of of %d cm', $this->_maxSum));
        }
    }
    catch (Mage_Shipping_Exception $error)
    {
        $this->setError($error);

        return false;
    }

    return true;
}

public function _getCustomWeight ($number)
{
    if ($number == 0)
    {
        $number = $this->_averageWeigth;
    }

    return !strcmp ($this->_weightUnit, 'kg') ? $number : ($number / 1000);
}

private function _getPackageWeigth($weight)
{
    $retorno = empty($weight) ? 0 : $weight;
    if (!$retorno > 0)
    {
        $retorno = $this->_boxAverageWeigth;
    }

    return $retorno;
}

private function _getHelper()
{
    return Mage::helper('basic');
}

}

