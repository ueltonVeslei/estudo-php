<?php

class Fooman_EmailAttachments_Model_Core_Email_Template_Mailer extends Mage_Core_Model_Email_Template_Mailer
{
    public function send()
    {
        $emailTemplate = Mage::getModel('core/email_template');
        // Send all emails from corresponding list
        while (!empty($this->_emailInfos)) {
            $emailInfo = array_pop($this->_emailInfos);
            $this->dispatchAttachEvent($emailTemplate);
            // Handle "Bcc" recepients of the current email
            $emailTemplate->addBcc($emailInfo->getBccEmails());
            // Set required design parameters and delegate email sending to Mage_Core_Model_Email_Template
            $emailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()))
                ->sendTransactional(
                $this->getTemplateId(),
                $this->getSender(),
                $emailInfo->getToEmails(),
                $emailInfo->getToNames(),
                $this->getTemplateParams(),
                $this->getStoreId()
            );
        }
        return $this;
    }

    public function dispatchAttachEvent($emailTemplate)
    {
        $storeId = $this->getStoreId();
        $templateParams = $this->getTemplateParams();

        //compare template id to work out what we are currently sending
        switch ( $this->getTemplateId()) {

            //Order
            case Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId):
            case Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId):
                Mage::dispatchEvent('fooman_emailattachments_before_send_order',
                                array(
                                    'update'=> false,
                                    'template' => $emailTemplate,
                                    'object' => $templateParams['order']
                                )
                );
                break;
            //Order Updates
            case Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId):
            case Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId):
                Mage::dispatchEvent('fooman_emailattachments_before_send_order',
                                array(
                                    'update'=> true,
                                    'template' => $emailTemplate,
                                    'object' => $templateParams['order']
                                )
                );
                break;

            //Invoice
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $storeId):
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId):
                Mage::dispatchEvent('fooman_emailattachments_before_send_invoice',
                                array(
                                    'update'=> false,
                                    'template' => $emailTemplate,
                                    'object' => $templateParams['invoice']
                                )
                );
                break;
            //Invoice Updates
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId):
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId):
                Mage::dispatchEvent('fooman_emailattachments_before_send_invoice',
                                array(
                                    'update'=> true,
                                    'template' => $emailTemplate,
                                    'object' => $templateParams['invoice']
                                )
                );
                break;

            //Shipment
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_TEMPLATE, $storeId):
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId):
                Mage::dispatchEvent('fooman_emailattachments_before_send_shipment',
                                array(
                                    'update'=> false,
                                    'template' => $emailTemplate,
                                    'object' => $templateParams['shipment']
                                )
                );
                break;
            //Shipment Updates
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId):
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId):
                Mage::dispatchEvent('fooman_emailattachments_before_send_shipment',
                                array(
                                    'update'=> true,
                                    'template' => $emailTemplate,
                                    'object' => $templateParams['shipment']
                                )
                );
                break;

            //Creditmemo
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_TEMPLATE, $storeId):
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId):
                Mage::dispatchEvent('fooman_emailattachments_before_send_creditmemo',
                                array(
                                    'update'=> false,
                                    'template' => $emailTemplate,
                                    'object' => $templateParams['creditmemo']
                                )
                );
                break;
            //Creditmemo Updates
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId):
            case Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId):
                Mage::dispatchEvent('fooman_emailattachments_before_send_creditmemo',
                                array(
                                    'update'=> true,
                                    'template' => $emailTemplate,
                                    'object' => $templateParams['creditmemo']
                                )
                );
        }
    }
}