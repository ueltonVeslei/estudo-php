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
class Magpleasure_Adminlogger_Model_Actiongroup_Salesshipments extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 19;
    const ACTION_SALES_SHIPMENTS_SAVE = 1;
    const ACTION_SALES_SHIPMENTS_LOAD = 2;
    const ACTION_SALES_SHIPMENTS_SEND_TRACKING_INFO = 3;
    const ACTION_SALES_SHIPMENTS_PRINT = 4;

    public function getLabel()
    {
        return $this->_helper()->__("Sales Shipments");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_SALES_SHIPMENTS_SAVE, 'label' => $this->_helper()->__("Create")),
            array('value' => self::ACTION_SALES_SHIPMENTS_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_SALES_SHIPMENTS_SEND_TRACKING_INFO, 'label' => $this->_helper()->__("Send Tracking Information")),
            array('value' => self::ACTION_SALES_SHIPMENTS_PRINT, 'label' => $this->_helper()->__("Print")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'sales/order_shipment';
    }

    public function getFieldKey()
    {
        return 'increment_id';
    }

    public function getUrlPath()
    {
        return 'adminhtml/sales_shipment/view';
    }

    public function getUrlIdKey()
    {
        return 'shipment_id';
    }

    public function getFieldPattern()
    {
        return "#%s";
    }
}