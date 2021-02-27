<?php

class Edrone_Base_EdroneproductController extends Mage_Core_Controller_Front_Action {
    public function indexAction(){

    }
    public function skuAction(){
        $sku = Mage::app()->getRequest()->getParam('v');
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if(count($parentIds) > 0){
            $product = Mage::getModel("catalog/product")->load( $parentIds[0] );
        }
        $productArray['sku'] = $product->getSku();
        $productArray['id'] = $product->getId();
        $productArray['title'] = $product->getName();
        $productArray['base_price'] = $product->getPrice();
        $productArray['final_price'] = $product->getFinalPrice();
        $productArray['image'] = (string)Mage::helper('catalog/image')->init($product, 'image')->keepFrame(false)->resize(null, 438);
        $productArray['product_url'] = $product->getUrlInStore();

        $categoryIds = $product->getCategoryIds();
        $productArray['product_category_ids'] = implode("~", $categoryIds);

        foreach ($categoryIds as $singleCategoryId) {
            if(is_numeric($singleCategoryId)){
                $category = Mage::getModel('catalog/category')->load(intval($singleCategoryId));
                $singleCategoryName = urlencode($category->getName());
                $productArray['product_category_names'] .= $singleCategoryName . "~";
            }
        }

        $productArray['product_category_names'] = substr($productArray['product_category_names'], 0, -1);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($productArray));
    }
    public function idAction(){
        $id = Mage::app()->getRequest()->getParam('v');
        $product = Mage::getModel('catalog/product')->load($id);
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if(count($parentIds) > 0){
            $product = Mage::getModel("catalog/product")->load( $parentIds[0] );
        }
        $productArray['sku'] = $product->getSku();
        $productArray['id'] = $product->getId();
        $productArray['title'] = $product->getName();
        $productArray['base_price'] = $product->getPrice();
        $productArray['final_price'] = $product->getFinalPrice();
        $productArray['image'] = (string)Mage::helper('catalog/image')->init($product, 'image')->keepFrame(false)->resize(null, 438);
        $productArray['product_url'] = $product->getUrlInStore();

        $categoryIds = $product->getCategoryIds();//array of product categories
        $productArray['product_category_ids'] = implode("~", $categoryIds);

        foreach ($categoryIds as $singleCategoryId) {
            if(is_numeric($singleCategoryId)){
                $category = Mage::getModel('catalog/category')->load(intval($singleCategoryId));
                $singleCategoryName = urlencode($category->getName());
                $productArray['product_category_names'] .= $singleCategoryName . "~";
                $tempIdList .=  $categoryIds . "~";
            }
        }
        $productArray['product_category_names'] = substr($productArray['product_category_names'], 0, -1);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($productArray));
    }
}
