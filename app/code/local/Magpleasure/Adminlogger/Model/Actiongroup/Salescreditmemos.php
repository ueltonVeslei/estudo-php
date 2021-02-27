<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Adminlogger
 * @copyright  Copyright (c) 2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */
class Magpleasure_Adminlogger_Model_Actiongroup_Salescreditmemos extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 21;

    const ACTION_SALES_CREDITMEMOS_CREATE = 1;
    const ACTION_SALES_CREDITMEMOS_LOAD = 2;
    const ACTION_SALES_CREDITMEMOS_EMAIL = 3;
    const ACTION_SALES_CREDITMEMOS_PRINT = 4;

    public function getLabel()
    {
        return $this->_helper()->__("Sales Credit Memos");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_SALES_CREDITMEMOS_CREATE, 'label' => $this->_helper()->__("Create")),
            array('value' => self::ACTION_SALES_CREDITMEMOS_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_SALES_CREDITMEMOS_EMAIL, 'label' => $this->_helper()->__("Send Email")),
            array('value' => self::ACTION_SALES_CREDITMEMOS_PRINT, 'label' => $this->_helper()->__("Print")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'sales/order_creditmemo';
    }

    public function getFieldKey()
    {
        return 'increment_id';
    }

    public function getUrlPath()
    {
        return 'adminhtml/sales_creditmemo/view';
    }

    public function getUrlIdKey()
    {
        return 'creditmemo_id';
    }

    public function getFieldPattern()
    {
        return "#%s";
    }
}