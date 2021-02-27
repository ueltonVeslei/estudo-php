<?php

class Fooman_PdfCustomiser_Model_Shipment extends Fooman_PdfCustomiser_Model_Abstract
{
    /**
    * Creates PDF using the tcpdf library from array of shipments or orderIds
    * @param array $shipmentsGiven, $orderIds
    * @access public
    */
    public function getPdf($shipmentsGiven = array(),$orderIds = array(),$pdf = null, $suppressOutput = false, $csvOutput=false)
    {

		if(empty($pdf) && empty($shipmentsGiven) && empty($orderIds)){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('There are no printable documents related to selected orders'));
			return false;
		}

        //we will be working through an array of orderIds later - fill it up if only invoices is given
        if(!empty($shipmentsGiven)){
            foreach ($shipmentsGiven as $shipmentGiven) {
                    $currentOrderId = $shipmentGiven->getOrder()->getId();
                    $orderIds[] = $currentOrderId;
                    $shipmentIds[$currentOrderId]=$shipmentGiven->getId();
            }
        }

        $this->_beforeGetPdf();

        $storeId = $order = Mage::getModel('sales/order')->load($orderIds[0])->getStoreId();

        //work with a new pdf or add to existing one
        if(empty($pdf)){
            $pdf = new Fooman_PdfCustomiser_Model_Mypdf('P', 'mm',  Mage::getStoreConfig('sales_pdf/all/allpagesize', $storeId), true, 'UTF-8', false);
        }
        $shipmentsCsv=array();
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if(!empty($shipmentsGiven)){
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->setOrderFilter($orderId)
                ->addAttributeToFilter('entity_id', $shipmentIds[$orderId])
                ->load();
            }else{
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->setOrderFilter($orderId)
                ->load();
            }            
            if ($shipments->getSize() > 0) {
                foreach ($shipments as $shipment) {
                    // create new Shipment helper
                    $shipmentHelper = new Fooman_PdfCustomiser_Shipment();
                    $shipment->load($shipment->getId());
                    $storeId = $shipment->getStoreId();
                    if ($shipment->getStoreId()) {
                        Mage::app()->getLocale()->emulate($shipment->getStoreId());
                    }

                    $shipmentHelper->setStoreId($storeId);
                    // set standard pdf info
                    $pdf->SetStandard($shipmentHelper);

                    // add a new page
                    $pdf->AddPage();
                    $pdf->printHeader($shipmentHelper, $shipmentHelper->getPdfShipmentTitle());

                    $shipmentNumbersEtc = Mage::helper('sales')->__('Packingslip # '). $shipment->getIncrementId()."\n";
                    if(Mage::getStoreConfig(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,$storeId)){
                        $shipmentNumbersEtc .= Mage::helper('sales')->__('Order # ') . $order->getIncrementId()."\n";
                    }

                    $shipmentNumbersEtc .= Mage::helper('catalog')->__('Date').': '.Mage::helper('core')->formatDate($shipment->getCreatedAt(), 'medium', false)."\n";
                    $pdf->MultiCell($pdf->getPageWidth() / 2 - $shipmentHelper->getPdfMargins('sides'), 0, $shipmentNumbersEtc, 0, 'L', 0, 0);
                    $pdf->MultiCell($pdf->getPageWidth() / 2 - $shipmentHelper->getPdfMargins('sides'), $pdf->getLastH(), $shipmentHelper->getPdfOwnerAddresss(), 0, 'L', 0, 1);
                    $pdf->Ln(5);

                    //add billing and shipping addresses
                    $pdf->OutputCustomerAddresses($shipmentHelper, $order, $shipmentHelper->getPdfShipmentAddresses());

                    // Output Shipping and Payment
                    $pdf->OutputPaymentAndShipping($shipmentHelper, $order,$shipment);
                    $shipmentsCsv[]=array(  'Name'          => $order->getShippingAddress()->getFirstname()." ".$order->getShippingAddress()->getLastname(),
                                            'Adress'        => implode(' \n ',$order->getShippingAddress()->getStreet()),
                                            'Postadress'    => $order->getShippingAddress()->getPostcode().$order->getShippingAddress()->getCity(),
                                            'Land'          => $order->getShippingAddress()->getCountryModel()->getName()
                                        );

                    // Output heading for Items
                    switch(Mage::getStoreConfig('sales_pdf/all/allpagesize',$storeId)){
                        case 'A4':
                            $units = (595 - 2.83*2*$shipmentHelper->getPdfMargins('sides'))/10;
                            break;
                        case 'LETTER':
                            $units = (612.00 - 2.83*2*(float)$shipmentHelper->getPdfMargins('sides'))/10;
                            break;
                    }

                    // Output heading for Items
                    $tbl ='<table border="0" cellpadding="2" cellspacing="0">';
                    $tbl.='<thead>';
                    $tbl.='<tr>';
                    $tbl.='<th width="'.(6.9*$units).'"><strong>'.Mage::helper('sales')->__('Name').'</strong></th>';
                    $tbl.='<th width="'.(2*$units).'"><strong>'.Mage::helper('sales')->__('SKU').'</strong></th>';
                    $tbl.='<th width="'.(1.1*$units).'" align="center"><strong>'.Mage::helper('sales')->__('QTY').'</strong></th>';
                    $tbl.='</tr>';
                    $tbl.='<tr><td width="'.(10*$units).'" colspan="6"><hr style="width:10px;"/></td></tr>';
                    $tbl.='</thead>';

                    // Prepare Line Items
                    $pdfItems = array();
                    $pdfBundleItems = array();
                    $pdf->prepareLineItems($shipmentHelper,$shipment->getAllItems(),$pdfItems,$pdfBundleItems);

                    //Output Line Items
                    $pdf->SetFont($shipmentHelper->getPdfFont(), '', $shipmentHelper->getPdfFontsize('small'));
                    foreach ($pdfItems as $pdfItem){

                        //we generallly don't want to display subitems of configurable products etc
                        if($pdfItem['parentItemId']){
                            continue;
                        }

                        //Output line items
                        if ($pdfItem['parentType'] != 'bundle' && $pdfItem['type'] != 'bundle') {

                            // Output 1 line item
                            $tbl.='<tr>';
                            $shipmentHelper->outputShippingLineItem($tbl,$shipmentHelper, Mage::getStoreConfig('sales_pdf/shipment/shipmentdisplay',$storeId), $pdf, $pdfItem,$units);
                            $tbl.='<td width="'.(2*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                            $tbl.='<td width="'.(1.1*$units).'"align="center">'.$pdfItem['qty'].'</td>';
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

                                // Output 1 bundle with subitems separately
                                $tbl.='<tr>';
                                $shipmentHelper->outputShippingLineItem($tbl, $shipmentHelper, Mage::getStoreConfig('sales_pdf/shipment/shipmentdisplay',$storeId),$pdf,$pdfItem,$units);
                                $tbl.='<td colspan="2" width="'.(2.75*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                                $tbl.='</tr>';
                                //Display subitems
                                foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                                    $tbl.='<tr>';
                                    // Output 1 subitem
                                    $bundleItem['productDetails']['Name']='&nbsp;&nbsp;&nbsp;&nbsp;'.$bundleItem['productDetails']['Name'];
                                    $shipmentHelper->outputShippingLineItem($tbl,$shipmentHelper, Mage::getStoreConfig('sales_pdf/shipment/shipmentdisplay',$storeId),$pdf,$bundleItem,$units,false);
                                    $tbl.='<td width="'.(2*$units).'">'.$bundleItem['productDetails']['Sku'].'</td>';
                                    $tbl.='<td width="'.(1.1*$units).'" align="center">'.$bundleItem['qty'].'</td>';
                                    $tbl.='</tr>';
                                }
                            }else {
                                foreach ($pdfBundleItems[$currentParentId] as $bundleItem){
                                    $pdfItem['productDetails']['Name'] .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;".$bundleItem['qty']." x " .$bundleItem['productDetails']['Name'];
                                }

                                // Output bundle with items as decription only
                                $tbl.='<tr>';
                                $shipmentHelper->outputShippingLineItem($tbl,$shipmentHelper, Mage::getStoreConfig('sales_pdf/shipment/shipmentdisplay',$storeId),$pdf,$pdfItem,$units);
                                $tbl.='<td width="'.(2*$units).'">'.$pdfItem['productDetails']['Sku'].'</td>';
                                $tbl.='<td width="'.(1.1*$units).'" align="center">'.$pdfItem['qty'].'</td>';
                                $tbl.='</tr>';
                            }
                        }
                        $tbl.='<tcpdf method="Line2" params=""/>';

                    }
                    $tbl.='</table>';
                    $pdf->writeHTML($tbl, true, false, false, false, '');
                    $pdf->SetFont($shipmentHelper->getPdfFont(), '', $shipmentHelper->getPdfFontsize());

                    //reset Margins in case there was a page break
                    $pdf->setMargins($shipmentHelper->getPdfMargins('sides'),$shipmentHelper->getPdfMargins('top'));

                    // Output Order Gift Message
                    $pdf->OutputGiftMessage($shipmentHelper, $order);

                    // Output Comments
                    $pdf->OutputComment($shipmentHelper,$shipment);

                    //Custom Blurb underneath
                    $pdf->Ln(2);
                    $pdf->writeHTMLCell(0, 0, null, null,$shipmentHelper->getPdfShipmentCustom(), null,1);
                    if ($shipment->getStoreId()) {
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
                if ($csvOutput){
                    $fileName ='shipments_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.csv';
                    $fp = fopen(Mage::getModel('core/config_options')->getExportDir().'/'.$fileName, 'w');

                    //create header line
                    fputcsv($fp,array_keys($shipmentsCsv[0]));

                    //output line items
                    foreach($shipmentsCsv as $csvLine) {
                        fputcsv($fp,$csvLine );
                    }
                    fclose($fp);

                    $fileNamePdf ='packingslip_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf';


                    ////add files to session message
                    Mage::getSingleton('adminhtml/session')->setCsvFilename($fileName);
                    Mage::getSingleton('adminhtml/session')->setPdfFilename($fileNamePdf);

                    //create pdf
                    $pdf->Output(Mage::getModel('core/config_options')->getExportDir().'/'.$fileNamePdf, 'F');
                }else{
                    $pdf->Output('packingslip_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'I');
                    exit;
                }
                
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

class Fooman_PdfCustomiser_Shipment extends Fooman_PdfCustomiser_Helper_Pdf {

   /**
     * get main heading for invoice title
     * @return  string
     * @access public
     */
    public function getPdfShipmentTitle(){
        return Mage::getStoreConfig('sales_pdf/shipment/shipmenttitle',$this->getStoreId());
    }

   /**
     * return which addresses to display
     * @return  string billing/shipping/both
     * @access public
     */
    public function getPdfShipmentAddresses(){
        return Mage::getStoreConfig('sales_pdf/shipment/shipmentaddresses',$this->getStoreId());
    }

    /**
     * custom text for underneath invoice
     * @return  string
     * @access public
     */
    public function getPdfShipmentCustom(){
        return Mage::getStoreConfig('sales_pdf/shipment/shipmentcustom',$this->getStoreId());
    }


   /**
     * output display of product on packing slip - optional display of image or barcode
     * @return  lineHeight
     * @access public
     */
    public function outputShippingLineItem(&$tbl,$helper, $display,&$pdf,$pdfItem,$units,$suppressBarcode = false){
        if($pdfItem['parentItemId']){
            $pdfItem['productDetails']['Name'] = "    ".$pdfItem['productDetails']['Name'];
        }
        switch($display) {
            case "image":
                $productImage = Mage::getModel('catalog/product')->load($pdfItem['productId'])->getImage();
                $imageHeight =18;
                if($productImage != "no_selection"){
                    $imageWidth = 0.15*($pdf->getPageWidth() - 2*$helper->getPdfMargins('sides'));
                    $nameWidth = 0.50;
                }else {
                    $nameWidth = 0.65;
                    $imageHeight = 0;
                }

                if($productImage != "no_selection"){
                    $tbl.='<td width="'.(3.9*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                    $imagePath = 'media/catalog/product'.$productImage;
                    $tbl.='<td align="center" width="'.(3*$units).'"><img src="'.$imagePath.'" width="'.(1.5*$units).'"/></td>';
               }else{
                    $tbl.='<td width="'.(6.9*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                }

                break;
            case "barcode":
                $tbl.='<td width="'.(3.9*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                $lineHeight =  $pdf->getLastH();
                if(!$suppressBarcode){
                    // CODE 39 EXTENDED + CHECKSUM
                    $tbl.='<td height="'.(1*$units).'" width="'.(3*$units).'"><tcpdf method="write1DBarcode" params="\''.$pdfItem['productDetails']['Sku'].'\',\'C39E+\',null,null,'.(0.8*$units).','.(0.35*$units).'"/></td>';
                }else{
                    $tbl.='<td width="'.(3*$units).'">&nbsp;</td>';
                }

                break;
            case "none":
                default:
                    $tbl.='<td width="'.(6.9*$units).'">'.$pdfItem['productDetails']['Name'].'</td>';
                }
        }
}