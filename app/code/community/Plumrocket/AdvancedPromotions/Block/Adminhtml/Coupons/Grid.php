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

class Plumrocket_Advancedpromotions_Block_Adminhtml_Coupons_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('coupons_and_orders_grid');
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getSingleton('pradvancedpromotions/index')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', array(
            'header' => Mage::helper('salesrule')->__('Rule ID'),
            'index'  => 'rule_id',
            'width'  => '50'
        ));

        $this->addColumn('rule_name', array(
            'header'    => Mage::helper('salesrule')->__('Rule Name'),
            'index'     => 'rule_name',
            'renderer'  => 'pradvancedpromotions/adminhtml_coupons_grid_renderer_rule',
        ));

        $this->addColumn('coupon_code', array(
            'header' => Mage::helper('salesrule')->__('Coupon Code'),
            'index'  => 'coupon_code'
        ));

        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'index'  => 'order_increment_id',
            'renderer'  => 'pradvancedpromotions/adminhtml_coupons_grid_renderer_order',
        ));

        $this->addColumn('gt', array(
            'header'    => Mage::helper('sales')->__('Grand Total (Base)'),
            'index'  => 'gt',
            'type' => 'number'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/promo_quote/edit', array('id' => $row->getRuleId()));
    }
}
