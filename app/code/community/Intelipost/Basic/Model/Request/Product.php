<?php

class Intelipost_Basic_Model_Request_Product
{

public $weight;
public $cost_of_goods;
public $width;
public $height;
public $length; 
public $quantity;
public $sku_id;
public $description;
public $can_group;  
public $product_category;

public function fetchQuoteProductRequest($packageArray)
{
    $this->weight = (float)$packageArray['weight'];
    $this->cost_of_goods = (float)$packageArray['price'];
    $this->width = (float)$packageArray['width'];
    $this->height = (float)$packageArray['height'];
    $this->length = (float)$packageArray['length'];
    $this->quantity = $packageArray['qty'];
    $this->sku_id   = $packageArray['sku'];
    $this->description = $packageArray['description'];
    $this->can_group = 0;

    $categories = Mage::helper ('basic')->getProductCategories ($packageArray ['id']);
    $result = null;
    foreach ($categories as $_category)
    {
        $result [] = $_category->getName ();
    }

    $this->product_category = implode (',', $result);

    return $this;
}

}

