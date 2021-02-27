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
class Magpleasure_Adminlogger_Adminhtml_AdminloggerController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->_title($this->__('Action Log'))
            ->loadLayout()
            ->_setActiveMenu('system/adminlogger');
        return $this;
    }

    public function indexAction()
    {
        $this
            ->_initAction()
            ->renderLayout();
    }

    public function gridAction()
    {
        $grid = $this->getLayout()->createBlock('adminlogger/adminhtml_adminlogger_grid');
        if ($grid) {
            $this->getResponse()->setBody($grid->toHtml());
        }
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $log = Mage::getModel('adminlogger/log')->load($id);
        if ($log->getId()) {
            Mage::register('adminlogger_data', $log);
            $this->loadLayout();
            $this->_addContent($this->getLayout()->createBlock('adminlogger/adminhtml_adminlogger_edit'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError(Mage::helper('adminlogger')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'actionlog.csv';
        $grid       = $this->getLayout()->createBlock('adminlogger/adminhtml_adminlogger_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'actionlog.xml';
        $grid       = $this->getLayout()->createBlock('adminlogger/adminhtml_adminlogger_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

}