<?php

class Onestic_Overcoupom_Adminhtml_SalesController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
       return true;
     }
     
    public function indexAction()
    {
        $this->_title($this->__('Cupons Promocionais'))->_title($this->__('Pedidos'));
        $this->loadLayout();
        $this->_setActiveMenu('promo/sales');
        $this->_addContent($this->getLayout()->createBlock('onestic_overcoupom/adminhtml_sales_order'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('onestic_overcoupom/adminhtml_sales_order_grid')->toHtml()
        );
    }

    public function exportOnesticCsvAction()
    {
        $fileName = 'pedidos_cupons.csv';
        $grid = $this->getLayout()->createBlock('onestic_overcoupom/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportOnesticExcelAction()
    {
        $fileName = 'pedidos_cupons.xml';
        $grid = $this->getLayout()->createBlock('onestic_overcoupom/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}