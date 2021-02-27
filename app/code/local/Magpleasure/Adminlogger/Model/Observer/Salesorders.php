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
class Magpleasure_Adminlogger_Model_Observer_Salesorders extends Magpleasure_Adminlogger_Model_Observer
{
    protected function _getOrderId()
    {
        return Mage::app()->getRequest()->getParam('order_id');
    }

    public function SalesOrdersLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_LOAD,
            $this->_getOrderId()
        );
    }

    public function SalesOrdersHold($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_HOLD,
            $this->_getOrderId()
        );
    }

    public function SalesOrdersUnhold($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_UNHOLD,
            $this->_getOrderId()
        );
    }

    public function SalesOrdersSendEmail($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_SEND_EMAIL,
            $this->_getOrderId()
        );
    }

    public function SalesOrdersCancel($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_CANCEL
        );
    }

    public function SalesOrdersMassCancel($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_MASS_CANCEL
        );
        $orderIds = Mage::app()->getRequest()->getPost('order_ids');
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($orderIds));
        }
    }

    public function SalesOrdersMassHold($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_MASS_HOLD
        );
        $orderIds = Mage::app()->getRequest()->getPost('order_ids');
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($orderIds));
        }
    }

    public function SalesOrdersMassUnhold($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_MASS_UNHOLD
        );
        $orderIds = Mage::app()->getRequest()->getPost('order_ids');
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($orderIds));
        }
    }

    public function SalesOrdersPdfInvoices($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_MASS_PDFINVOICES
        );
        $orderIds = Mage::app()->getRequest()->getPost('order_ids');
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($orderIds));
        }
    }

    public function SalesOrdersPdfShipments($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_MASS_PDFSHIPMENTS
        );
        $orderIds = Mage::app()->getRequest()->getPost('order_ids');
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($orderIds));
        }
    }

    public function SalesOrdersPdfCreditmemos($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_MASS_PDFCREDITMEMOS
        );
        $orderIds = Mage::app()->getRequest()->getPost('order_ids');
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($orderIds));
        }
    }

    public function SalesOrdersPdfAll($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_MASS_PDFALL
        );
        $orderIds = Mage::app()->getRequest()->getPost('order_ids');
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($orderIds));
        }
    }

    public function SalesOrdersPrintShippingLabels($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_MASS_PRINT_SHIPPING_LABELS
        );
        $orderIds = Mage::app()->getRequest()->getPost('order_ids');
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($orderIds));
        }
    }


    public function SalesOrdersSave($event)
    {
        $order = $event->getOrder();
        $log = $this->createLogRecord(
            $this->getActionGroup('salesorders')->getValue(),
            ($order->getId() ? Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_SAVE : Magpleasure_Adminlogger_Model_Actiongroup_Salesorders::ACTION_SALES_ORDERS_CREATE),
            $order->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($order->getData(), $order->getOrigData())
            );
        }
    }
}
