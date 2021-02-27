<?php
class Fooman_PdfCustomiser_Model_System_Fonts
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'courier', 'label'=>Mage::helper('pdfcustomiser')->__('Courier')),
            array('value'=>'times', 'label'=>Mage::helper('pdfcustomiser')->__('Times New Roman')),
            array('value'=>'helvetica', 'label'=>Mage::helper('pdfcustomiser')->__('Helvetica')),
            array('value'=>'dejavusans', 'label'=>Mage::helper('pdfcustomiser')->__('DejaVuSans')),
            array('value'=>'dejavusansmono', 'label'=>Mage::helper('pdfcustomiser')->__('DejaVuSansMono')),
            array('value'=>'dejavuserif', 'label'=>Mage::helper('pdfcustomiser')->__('DejaVuSerif'))
        );
    }


}