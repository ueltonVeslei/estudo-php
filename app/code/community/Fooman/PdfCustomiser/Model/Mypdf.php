<?php

//load the tcpdf library
require_once(BP. DS .'lib'. DS .'tcpdf'. DS .'tcpdf.php');

/*
 *  Extend the TCPDF class
 */

class Fooman_PdfCustomiser_Model_Mypdf extends TCPDF {


    /**
     * keep track if we have output
     * @access protected
     */
    protected $_PdfAnyOutput=false;

   /**
     * do we have output?
     * @return  bool
     * @access public
     */
    public function getPdfAnyOutput(){
        return $this->_PdfAnyOutput;
    }

   /**
     * set _PdfAnyOutput
     * @return  void
     * @access public
     */
    public function setPdfAnyOutput($flag){
        $this->_PdfAnyOutput = $flag;
    }

    /**
     * retrieve line items
     * @param
     * @return void
     * @access public
     */
    public function prepareLineItems($helper,$items,&$pdfItems,&$pdfBundleItems){
        foreach ($items as $item){
            //check if we are printing an order - doesn't have method getOrderItem
            if(method_exists($item,'getOrderItem')){
                //we generallly don't want to display subitems of configurable products etc but we do for bundled
                $type = $item->getOrderItem()->getProductType();
                $itemId = $item->getOrderItem()->getItemId();
                $parentType = 'none';
                $parentItemId = $item->getOrderItem()->getParentItemId();

                if($parentItemId){
                    $parentType = Mage::getModel('sales/order_item')->load($parentItemId)->getProductType();
                }

                //Get item Details
                $pdfTemp['itemId'] = $itemId;
                $pdfTemp['productId'] = $item->getOrderItem()->getProductId();
                $pdfTemp['type'] = $type;
                $pdfTemp['parentType'] = $parentType;
                $pdfTemp['parentItemId'] = $parentItemId;
                $pdfTemp['productDetails'] = $this->getItemNameAndSku($item);
                $pdfTemp['productOptions'] = $item->getProductOptions();
                $pdfTemp['price'] = $item->getPrice();
                $pdfTemp['discountAmount'] = $item->getDiscountAmount();
                $pdfTemp['qty'] = $helper->getPdfQtyAsInt()?(int)$item->getQty():$item->getQty();
                $pdfTemp['taxAmount'] = $item->getTaxAmount();
                $pdfTemp['rowTotal'] = $item->getRowTotal();
                $pdfTemp['basePrice'] = $item->getBasePrice();
                $pdfTemp['baseDiscountAmount'] = $item->getBaseDiscountAmount();
                $pdfTemp['baseTaxAmount'] = $item->getBaseTaxAmount();
                $pdfTemp['baseRowTotal'] = $item->getBaseRowTotal();

                //collect bundle subitems separately
                if($parentType == 'bundle'){
                    $pdfBundleItems[$parentItemId][]=$pdfTemp;
                }else{
                    $pdfItems[$itemId]=$pdfTemp;
                }

            }else {
                //we generallly don't want to display subitems of configurable products etc but we do for bundled
                $type = $item->getProductType();
                $itemId = $item->getItemId();
                $parentType = 'none';
                $parentItemId = $item->getParentItemId();

                if($parentItemId){
                    $parentType = Mage::getModel('sales/order_item')->load($parentItemId)->getProductType();
                }

                //Get item Details
                $pdfTemp['itemId'] = $itemId;
                $pdfTemp['productId'] = $item->getProductId();
                $pdfTemp['type'] = $type;
                $pdfTemp['parentType'] = $parentType;
                $pdfTemp['parentItemId'] = $parentItemId;
                $pdfTemp['productDetails'] = $this->getItemNameAndSku($item);
                $pdfTemp['productOptions'] = $item->getProductOptions();
                $pdfTemp['price'] = $item->getPrice();
                $pdfTemp['discountAmount'] = $item->getDiscountAmount();
                $pdfTemp['qty'] = $helper->getPdfQtyAsInt()?(int)$item->getQtyOrdered():$item->getQtyOrdered();
                $pdfTemp['taxAmount'] = $item->getTaxAmount();
                $pdfTemp['rowTotal'] = $item->getRowTotal();
                $pdfTemp['basePrice'] = $item->getBasePrice();
                $pdfTemp['baseDiscountAmount'] = $item->getBaseDiscountAmount();
                $pdfTemp['baseTaxAmount'] = $item->getBaseTaxAmount();
                $pdfTemp['baseRowTotal'] = $item->getBaseRowTotal();

                //collect bundle subitems separately
                if($parentType == 'bundle'){
                    $pdfBundleItems[$parentItemId][]=$pdfTemp;
                }else{
                    $pdfItems[$itemId]=$pdfTemp;
                }
            }
        }
    }

    /*
     * Page header
     * return float height of logo
     */

    public function printHeader($helper,$title) {

        $maxLogoHeight = 25;
        //add title
        $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize('large'));
        $this->Cell($helper->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0, $title, 0, 2, 'L',null,null,1);
        $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());

        // Place Logo
        if($helper->getPdfLogo()){
            //Figure out if logo is too wide - half the page width minus margins
            $maxWidth = ($helper->getPageWidth()/2) - $helper->getPdfMargins('sides');
            if($helper->getPdfLogoDimensions('w') > $maxWidth ){
                $logoWidth = $maxWidth;
            }else{
                $logoWidth = $helper->getPdfLogoDimensions('w');
            }
            $this->Image($helper->getPdfLogo(), $this->getPageWidth() - $helper->getPdfMargins('sides') - $logoWidth, $helper->getPdfMargins('top'), $logoWidth, $maxLogoHeight, null, null, null, null, null, null, null, null, null, true);
        }

        // Line break
        $this->SetY($helper->getPdfMargins('top')+min($helper->getPdfLogoDimensions('h-scaled'),$maxLogoHeight));
        $this->Ln(10);
    }

    /*
     *  set some standards for all pdf pages
     */
    public function SetStandard($helper){

        // set document information
        $this->SetCreator('Magento');

        //set margins
        $this->SetMargins($helper->getPdfMargins('sides'), $helper->getPdfMargins('top'));

        // set header and footer
        $this->setPrintFooter(false);
        $this->setPrintHeader(false);

        $this->setHeaderMargin(10);

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set auto page breaks
        $this->SetAutoPageBreak(true, $helper->getPdfMargins('bottom'));

        //set image scale factor 1 pixel = 1mm
        $this->setImageScale(1);

        // set font
        $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());

        // set fillcolor black
        $this->SetFillColor(0);

        // see if we need to sign
        if(Mage::getStoreConfig('sales_pdf/all/allsign',$helper->getStoreId())){
            $certificate = Mage::helper('core')->decrypt(Mage::getStoreConfig('sales_pdf/all/allsigncertificate',$helper->getStoreId()));
            $certpassword = Mage::helper('core')->decrypt(Mage::getStoreConfig('sales_pdf/all/allsignpassword',$helper->getStoreId()));

            // set document signature
            $this->setSignature($certificate, $certificate, $certpassword, '', 2, null);
        }

        //set Right to Left Language
        if(Mage::app()->getLocale()->getLocaleCode() == 'he_IL'){
            $this->setRTL(true);
        }else{
            $this->setRTL(false);
        }

    }

    public function Header() {

        //add title
        $headerData = $this->getHeaderData();

        $this->Cell(0, 0, $headerData['title'], 0, 2, 'L',null,null,1);


        // Line break
        $this->Ln(5);
    }

    public function Line2($space=1) {
        $this->SetY($this->GetY()+$space);
        $margins =$this->getMargins();
        $this->Line($margins['left'],$this->GetY(),$this->getPageWidth()-$margins['right'],$this->GetY());
        $this->SetY($this->GetY()+$space);

    }

    /*
     *  get product name and Sku, take into consideration configurable products and product options
     */
    public function getItemNameAndSku($item){
        $return = array();
        $return['Name'] = $item->getName();
        $return['Sku'] = $item->getSku();

        //check if we are printing an order - doesn't have method getOrderItem
        if(method_exists($item,'getOrderItem')){
            if ($options = $item->getOrderItem()->getProductOptions()) {
                if (isset($options['options'])) {
                    foreach ($options['options'] as $option){
                       $return['Name'] .= "<br/>&nbsp;&nbsp;".$option['label'].": ".$option['value'];
                    }
                    $return['Name'] .= "<br/>";
                }
                if (isset($options['additional_options'])) {
                    foreach ($options['additional_options'] as $additionalOption){
                       $return['Name'] .= "<br/>&nbsp;&nbsp;".$additionalOption['label'].": ".$additionalOption['value'];
                    }
                    $return['Name'] .= "<br/>";
                }
                if (isset($options['attributes_info'])) {
                    foreach ($options['attributes_info'] as $attribute){
                       $return['Name'] .= "<br/>&nbsp;&nbsp;".$attribute['label'].": ".$attribute['value'];
                    }
                }
                if($item->getOrderItem()->getProductOptionByCode('simple_sku')){
                    $return['Sku'] = $item->getOrderItem()->getProductOptionByCode('simple_sku');
                }
            }
        }else{
            if ($options = $item->getProductOptions()) {
                if (isset($options['options'])) {
                    foreach ($options['options'] as $option){
                       $return['Name'] .= "<br/>&nbsp;&nbsp;".$option['label'].": ".$option['value'];
                    }
                    $return['Name'] .= "<br/>";
                }
                if (isset($options['additional_options'])) {
                    foreach ($options['additional_options'] as $additionalOption){
                       $return['Name'] .= "<br/>&nbsp;&nbsp;".$additionalOption['label'].": ".$additionalOption['value'];
                    }
                    $return['Name'] .= "<br/>";
                }
                if (isset($options['attributes_info'])) {
                    foreach ($options['attributes_info'] as $attribute){
                       $return['Name'] .= "<br/>&nbsp;&nbsp;".$attribute['label'].": ".$attribute['value'];
                    }
                }
                if($item->getProductOptionByCode('simple_sku')){
                    $return['Sku'] = $item->getProductOptionByCode('simple_sku');
                }
            }
        }
        /*
        //Uncomment this block: delete /* and * / and enter your attribute code below
        $attributeCode ='attribute_code_from_Magento_backend';
        $productAttribute = Mage::getModel('catalog/product')->load($item->getProductId())->getData($attributeCode);
        if(!empty($productAttribute)){
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
            $return['Name'] .= "<br/><br/>".$attribute->getFrontendLabel().": ".$productAttribute;
        }
         */
        return $return;
    }
    
    public function getFormatAddress($address, $order, $type = 'billing'){
    	
    	if(!is_object($address)){
    		return;
    	}
    	
    	$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
    	
    	$street = $address->getStreet();
    	$nota = $customer->getData('is_nota_fiscal_paulista');
    	
    	$return  = $address->getName();
    	
    	if($address->getCountry() == 'BR'){
	    	$return .= '<br>' . ((isset($street[0]))? $street[0]: null) . ((isset($street[1]))? ', ' . $street[1]: null) . ((isset($street[2]))? ' - ' . $street[2]: null);
	    	$return .= ((isset($street[3]))? '<br>' . $street[3]: null);
	    	$return .= '<br>' . $address->getRegion();
    	}else{
    		$return .= ((isset($street[0]))? '<br>' . $street[0]: null);
    		$return .= '<br>' . $address->getRegion() .' - '. ((isset($street[2]))? $street[2]: null) . ((isset($street[1]))? ', '. $street[1]: null);
    		$return .= ((isset($street[3]))? '<br>' . $street[3]: null);
    		$return .= ((isset($street[4]))? '<br>' . $street[4]: null) . ' - ' . ((isset($street[5]))? $street[5]: null);
    	}
    	
    	$return .= '<br>' . $address->getCity() .', '. $address->getPostcode();
    	$return .= '<br>' . $address->getCountry();
    	$return .= '<br>T: ' . $address->getData('telephone');
    	$return .= '<br>F: ' . $address->getData('fax');
    	//$return .= '<br>' . $address->getGender();
    	$return .= '<br>Pessoa ' . $customer->getData('fisica_juridica');
    	$return .= '<br>CPF/CNPJ: ' .  $order->getCustomerTaxvat();//$customer->getData('taxvat');
    	$return .= '<br>Identidade/Inscri&ccedil;&atilde;o estadual: ' . $customer->getData('identidade_inscricao');
    	
    	if($type == 'billing'){
    		$return .= '<br>Nascimento: ' . Mage::getModel('core/date')->date('d/m/Y', $customer->getData('dob'));
    	}
    	$return .= '<br>Imprimir Nota Fiscal Paulista: ' . ((empty($nota))? 'N&atilde;o': $nota);
    	
    	return $return;
    	
    }

    /*
     *  output customer addresses
     */
    public function OutputCustomerAddresses($helper, $order, $which){

        $format = Mage::getStoreConfig('sales_pdf/all/alladdressformat',$helper->getStoreId());
        
        /* if($order->getCustomerTaxvat()){
            $billingAddress = $order->getBillingAddress()->format($format)."<br/>".Mage::helper('sales')->__('TAX/VAT Number').": ".$order->getCustomerTaxvat();
        }else{
            $billingAddress = $order->getBillingAddress()->format($format);
        } */
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());//->load('18405');
    	$customerCreatedAt = date('d/m/Y', strtotime($customer->getData('created_at')));  
        
        
        $billingAddress = $this->getFormatAddress($order->getBillingAddress(), $order);
        $shippingAddress = $this->getFormatAddress($order->getShippingAddress(), $order, 'shipping');
        
        //uncomment the next line (delete the //) to show the email address underneath the billing address
        $billingAddress.= "<br />Cliente desde: " .$customerCreatedAt; 
        $billingAddress.="<br/>".$order->getCustomerEmail();

        if (Mage::getStoreConfig('customer/create_account/permitir_desconto_saude')) {
            // baby info fields
            $babycnt = Mage::getStoreConfig('customer/create_account/numero_de_bebes');
            for ($i=0;$i<$babycnt;$i++) {
                $index = ($i+1);
                $get_baby_fullname = 'getBaby' . $index . 'Fullname';
            
                if ($_baby_fullname = $order->$get_baby_fullname()) {
                    $billingAddress .= "<br/>Nome completo do bebê ".$index.": ";
                    $billingAddress .= $_baby_fullname;
                }
                $get_baby_dob = 'getBaby' . $index . 'Dob';
                if ($_baby_dob = $order->$get_baby_dob()) {
                    $billingAddress .= "<br/>Data de nascimento do bebê ".$index.": ";
                    $billingAddress .= date('d/m/Y', strtotime($_baby_dob));
                }
                $get_baby_gender = 'getBaby' . $index . 'Gender';
                if ($_baby_gender = $order->$get_baby_gender()) {
                    $options = Mage::getResourceSingleton('customer/customer')->getAttribute('baby'.$index.'_gender')->getSource()->getAllOptions(); 
                    $billingAddress .= "<br/>Sexo do bebê ".$index.": ";
                    foreach ($options as $option) {
                        if ($option['value'] == $_baby_gender) {
                            $billingAddress .= $option['label'];
                            break;
                        }
                    }
                }
            }
            
            // health discount fields
            if ($_seguroSaudeIdentidade = $order->getSeguroSaudeIdentidade()) {
                $billingAddress .= "<br/>Número da carteira do plano de saúde: ";
                $billingAddress .= $_seguroSaudeIdentidade;
            }
            if ($_seguroSaudeNome = $order->getSeguroSaudeNome()) {
                $_options = Mage::getModel('customer/customer_attribute_source_group')->getAllOptions();
                $billingAddress .= "<br/>Nome do convênio: ";
                foreach($_options as $opt) {
                    if ($_seguroSaudeNome == $opt['value']) {
                        $billingAddress .= $opt['label'];
                    }
                }
            }
        }

        //which addresses are we supposed to show
        switch($which){
            case 'both':
                //swap order for Packing Slips - shipping on the left
                if(get_class($helper) =='Fooman_PdfCustomiser_Shipment'){
                    $this->SetX($helper->getPdfMargins('sides') + 5);
                    $this->Cell($this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0, Mage::helper('sales')->__('SHIP TO:'), 0, 0, 'L');
                    if(!$order->getIsVirtual()){
                        $this->Cell(0, 0, Mage::helper('sales')->__('SOLD TO:'), 0, 1, 'L');
                    }else{
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    $this->SetX($helper->getPdfMargins('sides') + 10);
                    $this->writeHTMLCell($this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0, null, null, $shippingAddress,null,0);
                    if(!$order->getIsVirtual()){
                        $this->writeHTMLCell(0, $this->getLastH(), null, null, $billingAddress,null,1);
                    }else{
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
					break;
                }else{
                    $this->SetX($helper->getPdfMargins('sides') + 5);
                    $this->Cell($this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0, Mage::helper('sales')->__('SOLD TO:'), 0, 0, 'L');
                    if(!$order->getIsVirtual()){
                        $this->Cell(0, 0, Mage::helper('sales')->__('SHIP TO:'), 0, 1, 'L');
                    }else{
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    $this->SetX($helper->getPdfMargins('sides') + 10);
                    $this->writeHTMLCell($this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0, null, null, $billingAddress,null,0);
                    if(!$order->getIsVirtual()){
                        $this->writeHTMLCell(0, $this->getLastH(), null, null, $shippingAddress,null,1);
					}else{
                        $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
                    }
                    break;
            }

            case 'billing':
                $this->SetX($helper->getPdfMargins('sides') + 5);
                $this->writeHTMLCell(0, 0, null, null, $billingAddress,null,1);
                break;
            case 'shipping':
                $this->SetX($helper->getPdfMargins('sides') + 5);
                if(!$order->getIsVirtual()){
                    //$this->writeHTMLCell(0, 0, null, null, $order->getShippingAddress()->format($format),null,1);
                    $this->writeHTMLCell(0, 0, null, null, $shippingAddress,null,1);
                }
                break;
            case 'singleshipping':
                $this->SetAutoPageBreak(false, 85);
                $this->SetXY(-180, -67);
                $this->writeHTMLCell(75, 0, null, null, $billingAddress,null,0);
                $this->SetAutoPageBreak(true, 85);
                break;
            case 'singlebilling':
                $this->SetAutoPageBreak(false, 85);
                $this->SetXY(-180, -67);
                if(!$order->getIsVirtual()){
                    $this->writeHTMLCell(75, $this->getLastH(), null, null, $shippingAddress,null,1);
                }
                $this->SetAutoPageBreak(true, 85);
                break;
            case 'double':
                $this->SetAutoPageBreak(false, 85);
                $this->SetXY(-180, -67);
                /*$this->Cell($this->getPageWidth() / 2 - $helper->getPdfMargins('sides'), 0, Mage::helper('sales')->__('SOLD TO:'), 0, 0, 'L');
                if(!$order->getIsVirtual()){
                    $this->Cell(0, 0, Mage::helper('sales')->__('SHIP TO:'), 0, 1, 'L');
                }*/
                $this->writeHTMLCell(75, 0, null, null, $billingAddress,null,0);
                $this->SetXY(-95, -67);
                if(!$order->getIsVirtual()){
                    $this->writeHTMLCell(75, $this->getLastH(), null, null, $shippingAddress,null,1);
                }
                $this->SetAutoPageBreak(true, 85);
                break;
            default:
                $this->SetX($helper->getPdfMargins('sides') + 5);
                $this->writeHTMLCell(0, 0, null, null, $billingAddress,null,1);
        }
        $this->Ln(10);
    }


    /*
     *  output payment and shipping blocks
     */
    public function OutputPaymentAndShipping($helper, $order){

        if(!Mage::registry('current_order')) {
            Mage::register('current_order',$order);
        }

       $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
                        ->setIsSecureMode(true)
                        ->toHtml();
                        //$paymentInfo = "";
        
         $dados_pagamento = "";
         $venda_id = $order->getRealOrderId();
        
		 $config = Mage::getConfig()->getResourceConnectionConfig('core_write');		  
         $conn = mysqli_connect($config->host,$config->username,$config->password) or die(mysqli_error());
		 if($conn)
			mysqli_select_db($conn,$config->dbname);                
 		  	
 		 $sql = "SELECT * FROM DadosPagamento WHERE Pedido = '$venda_id'";
 		 $query = mysqli_query($sql);
 		 $bool = false;
 		 
 		 if (mysqli_num_rows($query) > 0) {
     		  $dados = mysqli_fetch_object($query);
	            $bool = true;
				
     		  if ($dados->PagamentoTipo == 1) {
     		  	$dados_pagamento = "Cartão ";
     		 	$dados_pagamento .=  " - ".$this->bandeiraCodToLabel($dados->PagamentoInstituicao);
     		 	$dados_pagamento .= " - ".$dados->NumeroParcelas. " x ".Mage::helper('core')->currency(($dados->ValorParcelas),true,false) . "<br />";
     		 	$dados_pagamento .=  " Total: ".Mage::helper('core')->currency(($dados->ValorTotal),true,false);
     		 }
     		 else if ($dados->PagamentoTipo == 3) {
     		 	$dados_pagamento =  " - ".$this->bancoCodToLabel($dados->PagamentoInstituicao);
     		 	$dados_pagamento .=  "  -  Total: ".Mage::helper('core')->currency(($dados->ValorTotal),true,false);
     		 }
 		 }
 		 mysqli_close($conn);
        
        $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
        $this->Cell(0.5*($this->getPageWidth() - 2*$helper->getPdfMargins('sides')), 0, Mage::helper('sales')->__('Payment Method'), 0, 0, 'L');
        if(!$order->getIsVirtual()){
            $this->Cell(0, 0,Mage::helper('sales')->__('Shipping Method'), 0, 1, 'L');
        }else{
            $this->Cell(0, 0, '', 0, 1, 'L');
        }

        $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());
		
		if($bool){
      		  $this->writeHTMLCell(0.5*($this->getPageWidth() - 2*$helper->getPdfMargins('sides')), 0, null, null, /*$paymentInfo . */ " " . $dados_pagamento,null,0);
		}else
			$this->writeHTMLCell(0.5*($this->getPageWidth() - 2*$helper->getPdfMargins('sides')), 0, null, null, $paymentInfo . " " /*. $dados_pagamento*/,null,0);
		
        if(!$order->getIsVirtual()){
            $trackingInfo ="";
            $tracks = $order->getTracksCollection();
            if (count($tracks)) {
                $trackingInfo ="\n";
                foreach ($tracks as $track) {
                    $trackingInfo .="\n".$track->getTitle().": ".$track->getNumber();
                }
            }
            $this->MultiCell(0, $this->getLastH(), $order->getShippingDescription().$trackingInfo, 0, 'L', 0, 1);
        }else{
            $this->Cell(0, $this->getLastH(), '', 0, 1, 'L');
        }
        $this->Ln(10);
    }

    /*
     *  output totals for invoice and creditmemo
     */
    public function OutputTotals($helper, $order, $item){

        $labelTotalComJuros = false;
     	$totalComJuros = false;
     	$venda_id = $order->getRealOrderId();
        
		$config = Mage::getConfig()->getResourceConnectionConfig('core_write');		  
        $conn = mysqli_connect($config->host,$config->username,$config->password) or die(mysqli_error());
		if($conn)
		     mysqli_select_db($conn, $config->dbname);                
 		 	
 		$sql = "SELECT * FROM DadosPagamento WHERE Pedido = '$venda_id'";
 		$query = mysqli_query($conn, $sql);
 		 
     	
        if (mysqli_num_rows($query) > 0) {
     		  $dados = mysqli_fetch_object($query);
	            
     		 if ($dados->PagamentoTipo == 1 || $dados->PagamentoTipo == 3) {
     		    $labelTotalComJuros = "Total com juros:";
     		    $labelJuros = "Juros:";
     		 	$totalComJuros = (float) $dados->ValorTotal;
     		 }
 		 }
 		 mysqli_close($conn);

        //Display both currencies if flag is set and order is in a different currency
        $displayBoth = $helper->getDisplayBoth() && $order->isCurrencyDifferent();

        $widthTextTotals = $displayBoth ? $this->getPageWidth() - 2*$helper->getPdfMargins('sides') - 4.5*$helper->getPdfFontsize():
                                          $this->getPageWidth() - 2*$helper->getPdfMargins('sides') - 2.5*$helper->getPdfFontsize();
        $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__('Subtotal:'), 0, 'R', 0, 0);
        $this->OutputTotalPrice($order->getSubtotal(), $order->getBaseSubtotal(),$displayBoth,$order);
		
        if ((float)$order->getDiscountAmount() != 0){
        	$coupon = $order->getCouponCode() ? ' (' . $order->getCouponCode() . ')' : '';
            $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__('Desconto' . $coupon . ':'), 0, 'R', 0, 0);
            $this->OutputTotalPrice(abs($order->getDiscountAmount()), $order->getBaseDiscountAmount(),$displayBoth,$order);
        }

        if ((float)$order->getTaxAmount() > 0){
            if (Mage::helper('tax')->displayFullSummary()){
                $filteredTaxrates = array();
                //need to filter out doubled up taxrates on edited/reordered items -> Magento bug
                foreach ($order->getFullTaxInfo() as $taxrate){
                    foreach ($taxrate['rates'] as $rate){
                        $taxId= $rate['code'];
                        $filteredTaxrates[$taxId]= array('id'=>$rate['code'],'percent'=>$rate['percent'],'amount'=>$taxrate['amount'],'baseAmount'=>$taxrate['base_amount']);
                    }
                }
                foreach ($filteredTaxrates as $filteredTaxrate){
                    $this->MultiCell($widthTextTotals, 0, $filteredTaxrate['id']." [" .$filteredTaxrate['percent']."%]".":", 0, 'R', 0, 0);
                    $this->OutputTotalPrice($filteredTaxrate['amount'], $filteredTaxrate['baseAmount'],$displayBoth,$order);
                }
            }else{
                $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__('Tax').":", 0, 'R', 0, 0);
                $this->OutputTotalPrice($order->getTaxAmount(), $order->getBaseTaxAmount(),$displayBoth,$order);
            }
        }

        if ((float)$order->getShippingAmount() > 0){
            $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__('Envio e manuseio:'), 0, 'R', 0, 0);
            $this->OutputTotalPrice($order->getShippingAmount(), $order->getBaseShippingAmount(),$displayBoth,$order);
        }

        if ($order->getAdjustmentPositive()){
            $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__('Adjustment Refund:'), 0, 'R', 0, 0);
            $this->OutputTotalPrice($order->getAdjustmentPositive(), $order->getBaseAdjustmentPositive(),$displayBoth,$order);
        }

        if ((float) $order->getAdjustmentNegative()){
            $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__('Adjustment Fee:'), 0, 'R', 0, 0);
            $this->OutputTotalPrice($order->getAdjustmentNegative(), $order->getBaseAdjustmentNegative(),$displayBoth,$order);
        }

        //Total separated with line plus bolded
        $this->Ln(5);
        $this->Cell($this->getPageWidth()/2 - $helper->getPdfMargins('sides'), 5, '', 0, 0, 'C');
        $this->Cell(0, 5, '', 'T', 1, 'C');
        $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
        $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__('Grand Total:'), 0, 'R', 0, 0);
        $this->OutputTotalPrice($order->getGrandTotal(), $order->getBaseGrandTotal(),$displayBoth,$order);
        $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());
        
        // Com Juros
        if ( $totalComJuros )
        {
        	
            $valorJuros = ((float)$totalComJuros - (float) $order->getGrandTotal());
            $valorJuros = number_format($valorJuros, 2, ',', ' ');
            $this->SetFont($helper->getPdfFont(), 'N', $helper->getPdfFontsize());
            $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__($labelJuros), 0, 'R', 0, 0);
            $this->OutputTotalPrice($valorJuros, $valorJuros,false,$order);
            
            $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
            $this->MultiCell($widthTextTotals, 0, Mage::helper('sales')->__($labelTotalComJuros), 0, 'R', 0, 0);
            $this->OutputTotalPrice($totalComJuros, $totalComJuros,false,$order);
        }
    }
	
	// prints order increment id as barcode
	public function outPutOrderBarCode($helper, $order, $units) {
		$widthTextTotals = 50;
                $w = 0.8*$units;
		$h = 0.25*$units;
		$this->SetFont($helper->getPdfFont(), 'N', $helper->getPdfFontsize());
        $this->MultiCell($widthTextTotals, 0, $this->write1DBarcode($order->getIncrementId(), 'C39E+', null, null, $w, $h), 0, 'R', 0, 1);
	}

    /*
     *  output Gift Message for Order / Should work for Item but seems to be a bug in Magento (getGiftMessageId = null)
     */
    public function OutputGiftMessage($helper, $order){
		if ($order->getGiftMessageId() && $giftMessage = Mage::helper('giftmessage/message')->getGiftMessage($order->getGiftMessageId())){
            $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
            $this->Cell(0, 0, Mage::helper('giftmessage')->__('Mensagem de presente'), 0, 1, 'L',null,null,1);
            $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());

            $message = "<b>".Mage::helper('giftmessage')->__('De:')."</b> ".htmlspecialchars($giftMessage->getSender())."<br/>";
            $message .= "<b>".Mage::helper('giftmessage')->__('Para:')."</b> ".htmlspecialchars($giftMessage->getRecipient())."<br/>";
            $message .= "<b>".Mage::helper('giftmessage')->__('Mensagem:')."</b> ".htmlspecialchars($giftMessage->getMessage())."<br/>";
            $this->writeHTMLCell(0, 0, null, null,$message, null,1);
        }
    }

    /*
     *  output Comments on item - complete comment history
     *
     */
    public function OutputComment($helper, $item){
        if($helper->getPrintComments()){
            $comments ='';
            if (get_class($item) == 'Fooman_EmailAttachments_Model_Order'){
                foreach ($item->getAllStatusHistory() as $history){
                    $comments .=Mage::helper('core')->formatDate($history->getCreatedAt(), 'medium') ." | ".$history->getStatusLabel()."  ".$history->getComment()."\n";
                }
            }else{
                if ($item->getCommentsCollection()){
                    foreach($item->getCommentsCollection() as $comment){
                        $comments .=Mage::helper('core')->formatDate($comment->getCreatedAt(), 'medium') ." | ".$comment->getComment()."\n";
                    }

                }
            }
            if(!empty($comments)){
                $this->SetFont($helper->getPdfFont(), 'B', $helper->getPdfFontsize());
                $this->Cell(0, 0, Mage::helper('sales')->__('Comments'), 0, 1, 'L',null,null,1);
                $this->SetFont($helper->getPdfFont(), '', $helper->getPdfFontsize());
                $this->MultiCell(0, 0, $comments, 0, 'L', 0, 1);
            }
        }
    }

    /*
     *  output prices for invoice and creditmemo
     */
    public function OutputPrice($price, $basePrice,$displayBoth,$order)
    {

        return $displayBoth ? (strip_tags($order->formatBasePrice($basePrice)).'<br/>'.strip_tags($order->formatPrice($price)))
                        : $order->formatPriceTxt($price);
    }

    /*
     *  output total prices for invoice and creditmemo
     */
    public function OutputTotalPrice($price, $basePrice,$displayBoth,$order)
    {
        if($displayBoth){
            $this->MultiCell(2.25*$this->getFontSizePt(), 0, strip_tags($order->formatBasePrice($basePrice)), 0, 'R', 0, 0);
        }
        $this->MultiCell(0, 0, $order->formatPriceTxt($price), 0, 'R', 0, 1);
    }

    public function write1DBarcode($code, $type, $x='', $y='', $w='', $h='', $xres=0.4, $style='', $align='T') {
        $style =array(
        'position' => 'S',
        'border' => false,
        'padding' => 1,
        'fgcolor' => array(0,0,0),
        'bgcolor' => false,
        'text' => true,
        'font' => 'helvetica',
        'fontsize' => 8,
        'stretchtext' => 4
        );
        parent::write1DBarcode($code,$type, $x, $y, $w, $h, $xres, $style, $align);
    }


	public function bandeiraCodToLabel($cod)
	{
		return Mage::getModel( "flowecommerce_braspagcc/payment_gateway" )->bandeiraCodToLabel($cod);
	}
	
	public function bancoCodToLabel($cod)
	{
		return Mage::getModel( "flowecommerce_braspagdeb/payment_gateway" )->bancoCodToLabel($cod);
	}
}

