<?php

class Fooman_EmailAttachments_Model_Observer {

    public function addbutton($observer) {

        if($observer->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction ||
                $observer->getEvent()->getBlock() instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction) {
            $secure = Mage::app()->getStore()->isCurrentlySecure() ? 'true' : 'false';
            if($observer->getEvent()->getBlock()->getRequest()->getControllerName() =='sales_order') {
                $observer->getEvent()->getBlock()->addItem('pdforders_order', array(
                    'label'=> Mage::helper('emailattachments')->__('Print Orders'),
                    'url'  => Mage::helper('adminhtml')->getUrl('emailattachments/admin_order/pdforders',Mage::app()->getStore()->isCurrentlySecure() ? array('_secure'=>1) : array()),
                ));
            }
        }
    }

    public function beforeSendOrder ($observer)
    {
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $order = $observer->getEvent()->getObject();
        $configPath = $update ? 'order_comment' : 'order';

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachpdf', $order->getStoreId())) {
            //Create Pdf and attach to email - play nicely with PDF Customiser
            if ((string) Mage::getConfig()->getModuleConfig('Fooman_PdfCustomiser')->active == 'true') {
                $pdf = Mage::getModel('pdfcustomiser/order')->getPdf(array($order), null, null, true);
            } else {
                $pdf = Mage::getModel('emailattachments/order_pdf_order')->getPdf(array($order));
            }
            $mailTemplate = Mage::helper('emailattachments')->addAttachment($pdf, $mailTemplate, Mage::helper('sales')->__('Order') . "_" . $order->getIncrementId());
        }

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachagreement', $order->getStoreId())) {
            $mailTemplate = Mage::helper('emailattachments')->addAgreements($order->getStoreId(), $mailTemplate);
        }
    }

    public function beforeSendInvoice ($observer)
    {
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $invoice = $observer->getEvent()->getObject();
        $configPath = $update ? 'invoice_comment' : 'invoice';

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachpdf', $invoice->getStoreId())) {
            //Create Pdf and attach to email - play nicely with PDF Customiser
            $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($invoice), null, null, true);
            $mailTemplate = Mage::helper('emailattachments')->addAttachment($pdf, $mailTemplate, Mage::helper('sales')->__('Invoice') . "_" . $invoice->getIncrementId());
        }

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachagreement', $invoice->getStoreId())) {
            $mailTemplate = Mage::helper('emailattachments')->addAgreements($invoice->getStoreId(), $mailTemplate);
        }
    }

    public function beforeSendShipment ($observer)
    {
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $shipment = $observer->getEvent()->getObject();
        $configPath = $update ? 'shipment_comment' : 'shipment';

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachpdf', $shipment->getStoreId())) {
            //Create Pdf and attach to email - play nicely with PDF Customiser
            $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(array($shipment), null, null, true);
            $mailTemplate = Mage::helper('emailattachments')->addAttachment($pdf, $mailTemplate, Mage::helper('sales')->__('Shipment') . "_" . $shipment->getIncrementId());
        }

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachagreement', $shipment->getStoreId())) {
            $mailTemplate = Mage::helper('emailattachments')->addAgreements($shipment->getStoreId(), $mailTemplate);
        }
    }

    public function beforeSendCreditmemo ($observer)
    {
        $update = $observer->getEvent()->getUpdate();
        $mailTemplate = $observer->getEvent()->getTemplate();
        $creditmemo = $observer->getEvent()->getObject();
        $configPath = $update ? 'creditmemo_comment' : 'creditmemo';

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachpdf', $creditmemo->getStoreId())) {
            //Create Pdf and attach to email - play nicely with PDF Customiser
            $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf(array($creditmemo), null, null, true);
            $mailTemplate = Mage::helper('emailattachments')->addAttachment($pdf, $mailTemplate, Mage::helper('sales')->__('Credit Memo') . "_" . $creditmemo->getIncrementId());
        }

        if (Mage::getStoreConfig('sales_email/' . $configPath . '/attachagreement', $creditmemo->getStoreId())) {
            $mailTemplate = Mage::helper('emailattachments')->addAgreements($creditmemo->getStoreId(), $mailTemplate);
        }
    }

}