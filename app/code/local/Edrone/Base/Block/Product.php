<?php

class Edrone_Base_Block_Product extends Edrone_Base_Block_Base
{

    /**
     * @return array
     */
    public function getProductData()
    {
        $productArray = array();
        $product = Mage::registry('current_product');

        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if(count($parentIds) > 0){
            $product = Mage::getModel("catalog/product")->load( $parentIds[0] );
        }

        $productArray['sku'] = $product->getSku();
        $productArray['id'] = $product->getId();
        $productArray['title'] = $product->getName();
        $productArray['basePrice'] = $product->getPrice();
        $productArray['finalPrice'] = $product->getFinalPrice();
        $productArray['image'] = (string) Mage::helper('catalog/image')->init($product, 'image')->keepFrame(false)->resize(null, 438);
        $productArray['product_url'] = $product->getUrlInStore();

        $categoryIds = $product->getCategoryIds(); //array of product categories
        $productArray['product_category_ids'] = implode("~", $categoryIds);
        
        $productArray['product_category_names'] = '';
        foreach ($categoryIds as $singleCategoryId) {
            if(is_numeric($singleCategoryId)){
                $category = Mage::getModel('catalog/category')->load(intval($singleCategoryId));
                $singleCategoryName = urlencode($category->getName());
                $productArray['product_category_names'] .= $singleCategoryName . "~";
            }
        }
        $productArray['product_category_names'] = substr($productArray['product_category_names'], 0, -1);

        return $productArray;
    }
}