<?php

class Edrone_Base_Block_Cart extends Edrone_Base_Block_Base
{
    /**
     * @return array
     */
    public function getProductData()
    {
        $productData = array();
        $product = Mage::getModel('core/session')->getProductToShoppingCart();
        if ($product && $product->getSku()) {
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
            if(count($parentIds) > 0){
                $product = Mage::getModel("catalog/product")->load( $parentIds[0] );
                $title = $product->getName();
                $image = (string) Mage::helper('catalog/image')->init($product, 'image')->keepFrame(false)->resize(null, 438);
            }else{
                $title = $product->getTitle();
                $image = $product->getImage();
            }
            
            $productData['sku'] = $product->getSku();
            $productData['id'] = intval( Mage::getModel("catalog/product")->getIdBySku( $product->getSku() ) );
            $productData['title'] = $title;
            $productData['image'] = $image;            
            
            $_Product = Mage::getModel("catalog/product")->load( $productData['id']  );
            $categoryIds = $_Product->getCategoryIds();//array of product categories
            $productData['product_category_ids'] = implode("~", $categoryIds);            
            
            $catNamesArray = array();
            
            foreach ($categoryIds as $singleCategoryId) {
                $category = Mage::getModel('catalog/category')->load($singleCategoryId);
                array_push($catNamesArray,$category->getName());
            }
            $productData['product_category_names'] = implode("~", $catNamesArray);

            Mage::getModel('core/session')->unsProductToShoppingCart();
        }

        return $productData;
    }
}