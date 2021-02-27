<?php

class Fooman_PdfCustomiser_Model_Invoice extends Fooman_PdfCustomiser_Model_Abstract
{
    /**
    * Creates PDF using the tcpdf library from array of invoices or orderIds
    * @param array $invoices, $orderIds
    * @access public
    */
    public function getPdf($invoicesGiven = array(),$orderIds = array(), $pdf = null, $suppressOutput = false)
    {
		
		if(empty($pdf) && empty($invoicesGiven) && empty($orderIds)){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('There are no printable documents related to selected orders'));
			return false;
		}
		
        //we will be working through an array of orderIds later - fill it up if only invoices is given
        if(!empty($invoicesGiven)){
            foreach ($invoicesGiven as $invoiceGiven) {
                    $currentOrderId = $invoiceGiven->getOrder()->getId();
                    $orderIds[] = $currentOrderId;
                    $invoiceIds[$currentOrderId]=$invoiceGiven->getId();
            }
        }

        $this->_beforeGetPdf();

        //need to get the store id from the first order to intialise pdf
        $storeId = $order = Mage::getModel('sales/order')->load($orderIds[0])->getStoreId();

        //work with a new pdf or add to existing one
        if(empty($pdf)){
            $pdf = new Fooman_PdfCustomiser_Model_Mypdf('P', 'mm',  Mage::getStoreConfig('sales_pdf/all/allpagesize',$storeId), true, 'UTF-8', false);
        }

        foreach ($orderIds as $orderId) {
            //load data
			
            $order = Mage::getModel('sales/order')->load($orderId);
            if(!empty($invoicesGiven)){
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->addAttributeToFilter('entity_id', $invoiceIds[$orderId])
                    ->load();
            }else{
                /* $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($orderId)
                    ->load(); */
            	$invoices = $order->getInvoiceCollection();	
            }

            //loop over invoices
            if ($invoices->getSize() > 0) {
                foreach ($invoices as $invoice) {
                    // create new invoice helper
                    $invoiceHelper = new Fooman_PdfCustomiser_Invoice();
                    $invoice->load($invoice->getId());
                    $storeId = $invoice->getStoreId();
                    if ($invoice->getStoreId()) {
                        Mage::app()->getLocale()->emulate($invoice->getStoreId());
                    }

                    $invoiceHelper->setStoreId($storeId);
                    // set standard pdf info
                    $pdf->SetStandard($invoiceHelper);
                    if ($invoiceHelper->getPdfInvoiceIntegratedLabels()){
                        $pdf->SetAutoPageBreak(true, 85);
                    }
                    
                    // Output heading for Items
                    switch(Mage::getStoreConfig('sales_pdf/all/allpagesize',$storeId)){
                        case 'A4':
                            $units = (595 - 2.83*2*$invoiceHelper->getPdfMargins('sides'))/10;
                            break;
                        case 'LETTER':
                            $units = (612.00 - 2.83*2*(float)$invoiceHelper->getPdfMargins('sides'))/10;
                            break;
                    }

                    // add a new page
                    $pdf->AddPage();
                    
                    // Output order increment id barcode
		            $pdf->outPutOrderBarCode($invoiceHelper, $order, $units);
                    $pdf->Ln(8);
                    
                    $pdf->printHeader($invoiceHelper, $invoiceHelper->getPdfInvoiceTitle());

                    $invoiceNumbersEtc = Mage::helper('sales')->__('Fatura número '). $invoice->getIncrementId()."\n";
                    if(Mage::getStoreConfig(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,$storeId)){
                        $invoiceNumbersEtc .= Mage::helper('sales')->__('Pedido número ') . $order->getIncrementId()."\n";
                    }
                    if($invoiceHelper->getPdfInvoiceTaxNumber()){
                        $invoiceNumbersEtc .= $invoiceHelper->getPdfInvoiceTaxNumber()."\n";
                    }
                    $invoiceNumbersEtc .= Mage::helper('catalog')->__('Date').': '. Mage::helper('core')->formatDate($invoice->getCreatedAt(), 'medium', false)."\n";
                    if (Mage::getStoreConfig('sales_pdf/invoice/invoicedeliverydate',$storeId)){
                        $invoiceNumbersEtc .= Mage::helper('pdfcustomiser')->__('Delivery Date').': '.Mage::helper('core')->formatDate($invoice->getCreatedAt(), 'medium', false)."\n";
                    }
                    $pdf->Ln(10);
                    $pdf->MultiCell($pdf->getPageWidth() / 2 - $invoiceHelper->getPdfMargins('sides'), 0, $invoiceNumbersEtc, 0, 'L', 0, 0);
                    $pdf->MultiCell($pdf->getPageWidth() / 2 - $invoiceHelper->getPdfMargins('sides'), $pdf->getLastH(), $invoiceHelper->getPdfOwnerAddresss(), 0, 'L', 0, 1);
                    $pdf->Ln(12);

                    //add billing and shipping addresses
                    $pdf->OutputCustomerAddresses($invoiceHelper,$order, $invoiceHelper->getPdfInvoiceAddresses());

                    //Display both currencies if flag is set and order is in a different currency
                    $displayBoth = $invoiceHelper->getDisplayBoth() && $order->isCurrencyDifferent();

                    // Output Shipping and Payment
                    $pdf->OutputPaymentAndShipping($invoiceHelper,$order,$invoice);

                    
                    $tbl ='<table border="0" cellpadding="2" cellspacing="0">';
                    $tbl.='<thead>';
                    $tbl.='<tr>';
                        $tbl.='<th width="'.(3*$units).'"><strong>'.Mage::helper('sales')->__('Product').'</strong></th>';
                        $tbl.='<th width="'.(2*$units).'"><strong>'.Mage::helper('sales')->__('SKU').'</strong></th>';
                        $tbl.='<th width="'.(1.25*$units).'" align="center"><strong>'.Mage::helper('sales')->__('Price').'</strong></th>';
                        $tbl.='<th width="'.(1.25*$units).'" align="center"><strong>'.Mage::helper('sales')->__('QTY').'</strong></th>';
                        $tbl.='<th width="'.(1.25*$units).'" align="center"><strong>'.Mage::helper('sales')->__('Tax').'</strong></th>';
                        $tbl.='<th width="'.(1.25*$units).'" align="right"><strong>'.Mage::helper('sales')->__('Subtotal').'</strong></th>';
                    $tbl.='</tr>';
                    $tbl.='<tr><td width="'.(10*$units).'" colspan="6"><hr style="width:10px;"/></td></tr>';
                    $tbl.='</thead>';


                    // Prepare Line Items
                    $pdfItems = array();
                    $pdfBundleItems = array();
                    $pdf->prepareLineItems($invoiceHelper,$invoice->getAllItems(),$pdfItems,$pdfBundleItems);
                    
                    //Output Line Items
                    $pdf->SetFont($invoiceHelper->getPdfFont(), '', $invoiceHelper->getPdfFontsize('small'));
                    foreach ($pdfItems as $pdfItem){

                        //we generallly don't want to display subitems of configurable products etc 
                        if($pdfItem['parentItemId']){
                                continue;
                        }

                        //Output line items
                        if ($pdfItem['parentType'] != 'bundle' && $pdfItem['type'] != 'bundle') {
                            $tbl.='<tr>';
                                $tbl.='<td width="'.(3*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                                $tbl.='<td width="'.(2*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                                $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['price'],$pdfItem['basePrice'],$displayBoth,$order).'</td>';
                                $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdfItem['qty'].'</td>';
                                $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['taxAmount'],$pdfItem['baseTaxAmount'],$displayBoth,$order).'</td>';
                                $tbl.='<td width="'.(1.25*$units).'" align="right">'.$pdf->OutputPrice($pdfItem['rowTotal'],$pdfItem['baseRowTotal'],$displayBoth,$order).'</td>';
                            $tbl.='</tr>';
                            
                        } else {    //Deal with Bundles
                            //check if the subitems of the bundle have separate prices
                            $currentParentId =$pdfItem['itemId'];
                            $subItemsSum = 0;
                            foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                                $subItemsSum += $bundleItem['price'];
                            }
                            //don't display bundle price if subitems have prices
                            if( $subItemsSum > 0){
                                $tbl.='<tr>';
                                    $tbl.='<td width="'.(3*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                                    $tbl.='<td colspan="5" width="'.(7*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                                $tbl.='</tr>';
                                //Display subitems
                                foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                                    $tbl.='<tr>';
                                        $tbl.='<td width="'.(3*$units).'">&nbsp;&nbsp;&nbsp;&nbsp;'.$bundleItem['productDetails']['Name'].'</td>';
                                        $tbl.='<td width="'.(2*$units).'">'.$bundleItem['productDetails']['Sku'].'</td>';
                                        $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($bundleItem['price'],$bundleItem['basePrice'],$displayBoth,$order).'</td>';
                                        $tbl.='<td width="'.(1.25*$units).'" align="center">'.$bundleItem['qty'].'</td>';
                                        $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($bundleItem['taxAmount'],$bundleItem['baseTaxAmount'],$displayBoth,$order).'</td>';
                                        $tbl.='<td width="'.(1.25*$units).'" align="right">'.$pdf->OutputPrice($bundleItem['rowTotal'],$bundleItem['baseRowTotal'],$displayBoth,$order).'</td>';
                                    $tbl.='</tr>';
                                }
                            }else {
                                foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                                    $pdfItem['productDetails']['Name'] .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;".$bundleItem['qty']." x " .$bundleItem['productDetails']['Name'];
                                }
                                $tbl.='<tr>';
                                    $tbl.='<td width="'.(3*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                                    $tbl.='<td width="'.(2*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                                    $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['price'],$pdfItem['basePrice'],$displayBoth,$order).'</td>';
                                    $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdfItem['qty'].'</td>';
                                    $tbl.='<td width="'.(1.25*$units).'" align="center">'.$pdf->OutputPrice($pdfItem['taxAmount'],$pdfItem['baseTaxAmount'],$displayBoth,$order).'</td>';
                                    $tbl.='<td width="'.(1.25*$units).'" align="right">'.$pdf->OutputPrice($pdfItem['rowTotal'],$pdfItem['baseRowTotal'],$displayBoth,$order).'</td>';
                                $tbl.='</tr>';
                            }
                        }
                        $tbl.='<tcpdf method="Line2" params=""/>';
                    }                  
                    $tbl.='</table>';
                    $pdf->writeHTML($tbl, true, false, false, false, '');
                    
                    $pdf->OutputGiftMessage($invoiceHelper,$order);
                    
                    $pdf->SetFont($invoiceHelper->getPdfFont(), '', $invoiceHelper->getPdfFontsize());

                    //reset Margins in case there was a page break
                    $pdf->setMargins($invoiceHelper->getPdfMargins('sides'),$invoiceHelper->getPdfMargins('top'));

                    // Output totals
                    $pdf->OutputTotals($invoiceHelper,$order,$invoice);
					
					// Output Comments
                    $pdf->OutputComment($invoiceHelper,$invoice);

                    //Custom Blurb underneath
                    $pdf->Ln(2);
                    $pdf->writeHTMLCell(0, 0, null, null,$invoiceHelper->getPdfInvoiceCustom(), null,1);

                    /*
                    //Uncomment this block: delete /* and * / to add legal text for German invoices. EuVat Extension erforderlich
                    switch($order->getCustomerGroupId()){
                        case 2:
                            $pdf->Cell(0, 0, 'steuerfrei nach § 4 Nr. 1 b UStG', 0, 2, 'L',null,null,1);
                            break;
                        case 1:
                            $pdf->Cell(0, 0, 'umsatzsteuerfreie Ausfuhrlieferung', 0, 2, 'L',null,null,1);
                            break;
                    }
                     */
                    
                    if ($invoiceHelper->getPdfInvoiceIntegratedLabels()) {
                        $pdf->OutputCustomerAddresses($invoiceHelper,$order, $invoiceHelper->getPdfInvoiceIntegratedLabels());
                    }

                    if ($invoice->getStoreId()) {
                        Mage::app()->getLocale()->revert();
                    }
                    $pdf->setPdfAnyOutput(true);
                }
            }
        }

        // reset pointer to the last page
        $pdf->lastPage();

        //output PDF document
        if(!$suppressOutput) {
            if($pdf->getPdfAnyOutput()) {
                $pdf->Output('invoice_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'I');
                exit;
            }else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('There are no printable documents related to selected orders'));
            }
        }

        $this->_afterGetPdf();

        return $pdf;
    }

}

/*
 *  Extend the TCPDF class to create custom Header
 */

class Fooman_PdfCustomiser_Invoice extends Fooman_PdfCustomiser_Helper_Pdf{

   /**
     * get main heading for invoice title ie TAX INVOICE
     * @return  string
     * @access public
     */
    public function getPdfInvoiceTitle(){
        return Mage::getStoreConfig('sales_pdf/invoice/invoicetitle',$this->getStoreId());
    }

   /**
     * get tax number
     * @return  string
     * @access public
     */
    public function getPdfInvoiceTaxNumber(){
        return Mage::getStoreConfig('sales_pdf/invoice/invoicetaxnumber',$this->getStoreId());
    }

   /**
     * return which addresses to display
     * @return  string billing/shipping/both
     * @access public
     */
    public function getPdfInvoiceAddresses(){
        return Mage::getStoreConfig('sales_pdf/invoice/invoiceaddresses',$this->getStoreId());
    }

    /**
     * custom text for underneath invoice
     * @return  string
     * @access protected
     */

    public function getPdfInvoiceCustom(){
        return Mage::getStoreConfig('sales_pdf/invoice/invoicecustom',$this->getStoreId());
    }

    /**
     * are we using integrated labels - what to print?
     * @return  mixed bool / string
     * @access protected
     */

    public function getPdfInvoiceIntegratedLabels(){
        return Mage::getStoreConfig('sales_pdf/invoice/invoiceintegratedlabels',$this->getStoreId());
    }
}