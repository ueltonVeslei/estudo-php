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

class Plumrocket_AdvancedPromotions_Block_Adminhtml_Quote_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId   = 'page_id';
        $this->_blockGroup = 'pradvancedpromotions';
        $this->_controller = 'adminhtml_quote';
        $this->_mode = 'import';
        $this->_headerText = 'Import Shopping Cart Price Rules';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('pradvancedpromotions')->__('Import data'));
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/promo_quote/index');
    }
}
