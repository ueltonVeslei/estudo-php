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

class Magpleasure_Adminlogger_Model_Observer_Salesinvoices extends Magpleasure_Adminlogger_Model_Observer
{

    public function SalesInvoicesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salesinvoices')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesinvoices::ACTION_SALES_INVOICES_LOAD,
            Mage::app()->getRequest()->getParam('invoice_id')
        );
    }

    public function SalesInvoicesEmail($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salesinvoices')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesinvoices::ACTION_SALES_INVOICES_SEND_EMAIL,
            Mage::app()->getRequest()->getParam('invoice_id')
        );
    }

    public function SalesInvoicesPrint($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salesinvoices')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesinvoices::ACTION_SALES_INVOICES_PRINT,
            Mage::app()->getRequest()->getParam('invoice_id')
        );
    }

    public function SalesInvoicesSave($event)
    {
        $invoice = $event->getInvoice();
        if ($invoice) {
            $log = $this->createLogRecord(
                $this->getActionGroup('salesinvoices')->getValue(),
                Magpleasure_Adminlogger_Model_Actiongroup_Salesinvoices::ACTION_SALES_INVOICES_CREATE,
                $invoice->getId()
            );

            if ($log){
                $log->addDetails(
                    $this->_helper()->getCompare()->diff($invoice->getData(), array())
                );
            }
        }
    }
}
