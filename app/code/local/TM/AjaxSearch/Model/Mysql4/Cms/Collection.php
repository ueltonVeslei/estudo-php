<?php
class TM_AjaxSearch_Model_Mysql4_Cms_Collection extends Mage_Cms_Model_Resource_Page_Collection
{
    const ATTRIBUTES_CONF = 'tm_ajaxsearch/general/cms_attributes';

    protected function _setQueryFilter($query, $field = 'title')
    {
        $andWhere = array();
        foreach (explode(' ', trim($query)) as $word) {

            $this->addFieldToFilter(
                $field,
                array('like'=> '%' . $word .'%')
            );

            $andWhere[] = $this->_getConditionSql(
                $field,
                array('like' => '%' . $word . '%')
            );
        }
        $this->getSelect()->orWhere(implode(' AND ', $andWhere));
    }

    public function setQueryFilter($query)
    {
        $fields = Mage::getStoreConfig(self::ATTRIBUTES_CONF);
        $fields = explode(',', $fields);
        foreach ($fields as $field) {
            $this->_setQueryFilter($query, $field);
        }

        return $this;
    }
}
