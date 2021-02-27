<?php
class TM_AjaxSearch_Model_Layer extends Mage_CatalogSearch_Model_Layer
{
    const USE_CATALOGSEARCH_COLLECTION_CONFIG = 'tm_ajaxsearch/general/use_catalogsearch_collection';

    /**
     * Get current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = $this->initCollection();
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }
        return $collection;
    }

    protected function initCollection()
    {
        $isStandartCollection = (bool) Mage::getStoreConfig(self::USE_CATALOGSEARCH_COLLECTION_CONFIG);
        // $isStandartCollection = true;
        if ($isStandartCollection) {
            $collection = Mage::getResourceModel('catalogsearch/fulltext_collection');
            $this->prepareProductCollection($collection);
        } else {
            $query = Mage::helper('catalogsearch')->getQuery()->getQueryText();
            $collection = Mage::getResourceModel('ajaxsearch/product_collection')
                ->addAttributeToSelect(
                    Mage::getSingleton('catalog/config')->getProductAttributes()
                )
                ->setQueryFilter($query)
                ;
            /* @var $collection TM_AjaxSearch_Model_Mysql4_Collection */
            $collection->setStore(Mage::app()->getStore())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addStoreFilter()
                ->addUrlRewrite();

            Mage::getSingleton('catalog/product_status')
                ->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInSearchFilterToCollection($collection);
        }
        // $this->prepareProductCollection($collection);

        return $collection;
    }
}
