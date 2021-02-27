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

class Plumrocket_AdvancedPromotions_Adminhtml_Prpromo_CouponsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        if (!Mage::getStoreConfig(Plumrocket_AdvancedPromotions_Helper_Data::RUNTIME_CONFIG_KEY)) {
            Mage::getSingleton('pradvancedpromotions/index')->reindex();
        }

        Mage::getSingleton('adminhtml/session')->addNotice(
            Mage::helper('core')->__('Last updated: %s. To refresh last Orders & Coupons data, click <a href="%s">here</a>.',
                date('M d, Y g:i:s A', Mage::getStoreConfig(Plumrocket_AdvancedPromotions_Helper_Data::RUNTIME_CONFIG_KEY)),
                Mage::helper('adminhtml')->getUrl('*/*/refresh')
            )
        );
        $this->loadLayout()
            ->_setActiveMenu('promo')
            ->_title($this->__('Orders &amp; Coupons'));

        $this->renderLayout();
    }

    public function refreshAction()
    {
        try {
            Mage::getSingleton('pradvancedpromotions/index')->reindex();
            Mage::getSingleton('adminhtml/session')->addSuccess('Data refreshed successfully.');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Cannot refresh data. '. $e->getMessage());
        }

        $this->_redirectReferer();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/quote/shopping_cart_coupons');
    }
}