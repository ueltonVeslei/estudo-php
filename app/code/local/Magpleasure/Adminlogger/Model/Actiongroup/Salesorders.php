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
class Magpleasure_Adminlogger_Model_Actiongroup_Salesorders extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 18;
    const ACTION_SALES_ORDERS_CREATE = 1;
    const ACTION_SALES_ORDERS_SAVE = 2;
    const ACTION_SALES_ORDERS_LOAD = 3;
    const ACTION_SALES_ORDERS_HOLD = 4;
    const ACTION_SALES_ORDERS_UNHOLD = 5;
    const ACTION_SALES_ORDERS_SEND_EMAIL = 6;
    const ACTION_SALES_ORDERS_CANCEL = 7;
    const ACTION_SALES_ORDERS_MASS_CANCEL = 8;
    const ACTION_SALES_ORDERS_MASS_HOLD = 9;
    const ACTION_SALES_ORDERS_MASS_UNHOLD = 10;
    const ACTION_SALES_ORDERS_MASS_PDFINVOICES = 11;
    const ACTION_SALES_ORDERS_MASS_PDFSHIPMENTS = 12;
    const ACTION_SALES_ORDERS_MASS_PDFCREDITMEMOS = 13;
    const ACTION_SALES_ORDERS_MASS_PDFALL = 14;
    const ACTION_SALES_ORDERS_MASS_PRINT_SHIPPING_LABELS = 15;

    public function getLabel()
    {
        return $this->_helper()->__("Sales Orders");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_SALES_ORDERS_CREATE, 'label' => $this->_helper()->__("Create")),
            array('value' => self::ACTION_SALES_ORDERS_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_SALES_ORDERS_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_SALES_ORDERS_HOLD, 'label' => $this->_helper()->__("Hold")),
            array('value' => self::ACTION_SALES_ORDERS_MASS_HOLD, 'label' => $this->_helper()->__("Mass Hold")),
            array('value' => self::ACTION_SALES_ORDERS_UNHOLD, 'label' => $this->_helper()->__("Unhold")),
            array('value' => self::ACTION_SALES_ORDERS_MASS_UNHOLD, 'label' => $this->_helper()->__("Mass Unhold")),
            array('value' => self::ACTION_SALES_ORDERS_SEND_EMAIL, 'label' => $this->_helper()->__("Send Email")),
            array('value' => self::ACTION_SALES_ORDERS_CANCEL, 'label' => $this->_helper()->__("Cancel")),
            array('value' => self::ACTION_SALES_ORDERS_MASS_CANCEL, 'label' => $this->_helper()->__("Mass Cancel")),
            array('value' => self::ACTION_SALES_ORDERS_MASS_PDFINVOICES, 'label' => $this->_helper()->__("Mass Print Invoices")),
            array('value' => self::ACTION_SALES_ORDERS_MASS_PDFSHIPMENTS, 'label' => $this->_helper()->__("Mass Print Packingslips")),
            array('value' => self::ACTION_SALES_ORDERS_MASS_PDFCREDITMEMOS, 'label' => $this->_helper()->__("Mass Print Credit Memos")),
            array('value' => self::ACTION_SALES_ORDERS_MASS_PDFALL, 'label' => $this->_helper()->__("Mass Print All")),
            array('value' => self::ACTION_SALES_ORDERS_MASS_PRINT_SHIPPING_LABELS, 'label' => $this->_helper()->__("Mass Print Shipping Labels")),
        );
    }

    public function getDetailsRenderer($type = false)
    {
        $massActions = array(
            self::ACTION_SALES_ORDERS_MASS_CANCEL,
            self::ACTION_SALES_ORDERS_MASS_HOLD,
            self::ACTION_SALES_ORDERS_MASS_UNHOLD,
            self::ACTION_SALES_ORDERS_MASS_PDFALL,
            self::ACTION_SALES_ORDERS_MASS_PDFCREDITMEMOS,
            self::ACTION_SALES_ORDERS_MASS_PDFINVOICES,
            self::ACTION_SALES_ORDERS_MASS_PDFSHIPMENTS,
            self::ACTION_SALES_ORDERS_MASS_PRINT_SHIPPING_LABELS,
        );
        if (in_array($type, $massActions)){
            return 'orders';
        } else {
            return parent::getDetailsRenderer($type);
        }
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'sales/order';
    }

    public function getFieldKey()
    {
        return 'increment_id';
    }

    public function getUrlPath()
    {
        return 'adminhtml/sales_order/view';
    }

    public function getUrlIdKey()
    {
        return 'order_id';
    }

    public function getFieldPattern()
    {
        return "#%s";
    }
}