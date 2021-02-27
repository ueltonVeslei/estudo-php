<?php

class TM_AjaxSearch_Model_Mysql4_Product_Collection extends
Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
// Mage_CatalogSearch_Model_Resource_Fulltext_Collection
{
    public function addSearchFilter($query)
    {
        return $this->setQueryFilter($query);
    }

    protected function _getEnabledAttributteCodes()
    {
        $attributes = array('name');
        $searchAttributes = Mage::getStoreConfig('tm_ajaxsearch/general/attributes');
        if (!empty($searchAttributes)) {
            $attributes = explode(',', $searchAttributes);
        }
        return $attributes;
    }

    public function setQueryFilter($query)
    {
        $attributes = $this->_getEnabledAttributteCodes();
        $andWhere = array();
        $orWhere = false;

        /* @var $stringHelper Mage_Core_Helper_String */
        $stringHelper = Mage::helper('core/string');

        $words = $stringHelper->splitWords($query, true);
//        $words = explode(' ', trim($query));
        if (empty($words) || empty($query)) {
            return $this;
        }
        $condition = array();
        foreach ($attributes as $attribute) {
            $this->addAttributeToSelect($attribute, true);
            foreach ($words as $word) {
                $andWhere[] = $this->_getAttributeConditionSql(
                    $attribute,
                    array('like' => '%' . $word . '%')
                );
            }
            $condition[] = implode(' AND ', $andWhere);
            $andWhere = array();
        }

        $this->getSelect()->where(implode($condition, ' OR '));

        return $this;
    }

    protected function _preparePriceExpressionParameters($select)
    {
        $isStandartCollection = (bool) Mage::getStoreConfig(
                TM_AjaxSearch_Model_Layer::USE_CATALOGSEARCH_COLLECTION_CONFIG
            );

        if ($isStandartCollection) {
            // use standart collection
            return parent::_preparePriceExpressionParameters($select);
        }

        // solve compatibility with layered navigation
        // inspirede by magento fulltext collection (select only specific entity_ids)
        $read = $this->getResource()->getReadConnection();
        $columns = $select->getPart(Zend_Db_Select::COLUMNS);
        if (empty($columns)) {
            // workaround for Ajax Layared Naviagtion (not normal behavior)
            $ids = $read->fetchCol($this->getSelect());
        } else {
            $ids = $read->fetchCol($select);
        }
        $select->reset(Zend_Db_Select::WHERE);
        $select->where('e.entity_id in (?)', $ids);

        return parent::_preparePriceExpressionParameters($select);
    }

}
