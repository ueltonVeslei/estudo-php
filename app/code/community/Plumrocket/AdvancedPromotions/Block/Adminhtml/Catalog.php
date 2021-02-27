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

class Plumrocket_AdvancedPromotions_Block_Adminhtml_Catalog extends Mage_Adminhtml_Block_Promo_Catalog
{
    protected function _prepareLayout()
    {
        /*$this->_addButton('add_new', array(
            'label'   => Mage::helper('catalog')->__('Import Rules'),
            'onclick' => "setLocation('{$this->getUrl('adminhtml/prpromo_catalog/import')}')",
        ));*/

        return parent::_prepareLayout();
    }
}
