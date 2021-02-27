<?php

class Fooman_EmailAttachments_Model_Email_Template extends Aschroder_SMTPPro_Model_Email_Template
{
    public function addAttachment($pdf, $name = "order.pdf"){
        $file = $pdf->render();
        $this->getMail()->createAttachment($file,'application/pdf',Zend_Mime::DISPOSITION_ATTACHMENT,Zend_Mime::ENCODING_BASE64,$name.'.pdf');
    }

    public function addAgreements($storeId){
        $agreements = Mage::getModel('checkout/agreement')->getCollection()
            ->addStoreFilter($storeId)
            ->addFieldToFilter('is_active', 1);
        if ($agreements){
            foreach ($agreements as $agreement){
                $agreement->load($agreement->getId());
                if($agreement->getIsHtml()){
                    $html='<html><head><title>'.$agreement->getName().'</title></head><body>'.$agreement->getContent().'</body></html>';
                    $this->getMail()->createAttachment($html,'text/html',Zend_Mime::DISPOSITION_ATTACHMENT,Zend_Mime::ENCODING_BASE64,urlencode($agreement->getName()).'.html');
                } else{
                    $this->getMail()->createAttachment(Mage::helper('core')->htmlEscape($agreement->getContent()),'text/plain',Zend_Mime::DISPOSITION_ATTACHMENT,Zend_Mime::ENCODING_BASE64,urlencode($agreement->getName()).'.txt');
                }
            }
        }
    }
}