<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_AdvancedPromotions
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

class Plumrocket_AdvancedPromotions_Model_Mysql4_Index extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('pradvancedpromotions/index', 'index_id');
    }

    public function reindex()
    {
        $resource = Mage::getSingleton('core/resource');

        $this->_getWriteAdapter()->query('DELETE FROM ' . $resource->getTableName('pradvancedpromotions/index'));

        $collection = new Plumrocket_AdvancedPromotions_Model_Mysql4_Grid_Collection;
        $collection
            ->addFieldToSelect('rule_id')
            ->addFieldToSelect('name');
        $collection->getSelect()
            ->joinLeft(array('cp' => $resource->getTableName('salesrule/coupon')),
             'main_table.rule_id = cp.rule_id',
             array('code')
        )->joinLeft(array('o' => $resource->getTableName('sales/order')),
            'FIND_IN_SET(main_table.rule_id, o.applied_rule_ids) AND o.coupon_code IS NULL
            or o.coupon_code = cp.code
             ',
             array('entity_id', 'increment_id', 'base_grand_total')
        )->where('cp.code IS NOT NULL OR o.increment_id IS NOT NULL');

        $collection->addFilterToMap('code', 'cp.code');

        $query = 'INSERT INTO ' . $resource->getTableName('pradvancedpromotions/index'). ' (rule_id, rule_name, coupon_code, order_id, order_increment_id, gt) ' .
            (string) $collection->getSelect();

        $this->_getWriteAdapter()->query($query);

        Mage::getConfig()->saveConfig(
            Plumrocket_AdvancedPromotions_Helper_Data::RUNTIME_CONFIG_KEY,
            Mage::getSingleton('core/date')->timestamp(),
            'default',
            0
        )->reinit();

    }
}