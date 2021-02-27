<?php

class Dio5_Multiproducts_Block_Catalog_Product_Widget_Multiproducts extends Mage_Catalog_Block_Product_Abstract implements Mage_Widget_Block_Interface {

    protected function _beforeToHtml() {

        $ids = $this->getData('ids');
        if ($ids) {
            $ids = explode('}{', $ids);
            $cleanIds = array();
            foreach ($ids as $id) {
                $id = str_replace('{', '', $id);
                $id = str_replace('}', '', $id);
                $cleanIds[] = $id;
            }
            if (count($cleanIds)) {
                $products = $this->_getProductsByIDs($cleanIds);
                if ($products) {
                    $this->setProductCollection($products);
                }
            }
        }

        return parent::_beforeToHtml();
    }

    protected function _getProductsByIDs($ids) {
        $products = Mage::getModel('catalog/product')->getResourceCollection();
        $products->addAttributeToSelect('*');
        $products->addStoreFilter(Mage::app()->getStore()->getId());
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($products);
        //Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
        $products->addFieldToFilter('entity_id', array('in' => $ids));
        $products->addAttributeToSort('entity_id', 'desc');
        $products->load();
        return $products;
    }

}