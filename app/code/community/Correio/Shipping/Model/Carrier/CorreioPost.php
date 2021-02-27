<?php

/**
 * Correios shipping model
 *
 * @category   Correio
 * @package    Correio_Shipping
 * @author     Igor Pfeilsticker <igorsop@gmail.com>
 */
 
 
class Parameters { }

class Correio_Shipping_Model_Carrier_CorreioPost
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'correiopost';
	
	protected $_result = null;
	
	/**
		 * Check if current carrier offer support to tracking
	 *
	 * @return boolean true
	 */
	public function isTrackingAvailable() {
		return true;
	}

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
		
    	if (!$this->getConfigFlag('active'))
		{
			//Disabled
			return false;
		}
		
		

		$origCountry = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());
		if ($origCountry != "BR"){
			//Out of delivery area
			return false;
		}
		
		
		$result = Mage::getModel('shipping/rate_result');
		
		$error = Mage::getModel('shipping/rate_result_error');
		$error->setCarrier($this->_code);
		$error->setCarrierTitle($this->getConfigData('title'));


		$packagevalue = $request->getBaseCurrency()->convert($request->getPackageValue(), $request->getPackageCurrency());
		$minorderval = $this->getConfigData('min_order_value');
		$maxorderval = $this->getConfigData('max_order_value');
		if($packagevalue <= $minorderval || $packagevalue >= $maxorderval){
			//Value limits
			$error->setErrorMessage($this->getConfigData('valueerror'));
			$result->append($error);
			return $result;
		}

		$frompcode = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());
		$topcode = $request->getDestPostcode();
		
		//Fix Zip Code
		$frompcode = str_replace('-', '', trim($frompcode));
		$topcode = str_replace('-', '', trim($topcode));

		if(!preg_match("/^[0-9]{8}$/", $topcode))
		{
			//Invalid Zip Code
			$error->setErrorMessage($this->getConfigData('zipcodeerror'));
			$result->append($error);
			Mage::helper('customer')->__('Invalid ZIP CODE');
			return $result;
		}
		
		
		$sweight = $request->getPackageWeight();

		if ($sweight > $this->getConfigData('maxweight')){
			//Weight exceeded limit
			$error->setErrorMessage($this->getConfigData('maxweighterror'));
			$result->append($error);
			return $result;
		}
		
		
		//Define post method
		$shipping_methods = array();
							
		$postmethods = explode(",", $this->getConfigData('postmethods'));
		
		foreach($postmethods as $methods)
		{
		
			switch ($methods){
					case 0:
						$shipping_methods["41106"] = array ("PAC", "3 a 8");
						break;
					case 1:
						$shipping_methods["40010"] = array ("Sedex", "1 a 3");
						break;
					case 2:
						$shipping_methods["40215"] = array ("Sedex 10", "1");
						break;
					case 3:
						$shipping_methods["40290"] = array ("Sedex HOJE", "1");
						break;
					case 4:
						$shipping_methods["81019"] = array ("E-Sedex", "2 a 5");
						break;
			}
		}
		
		foreach($shipping_methods as $shipping_method => $shipping_values){
        
            //Define URL method
			switch ($this->getConfigData('urlmethod')){
				
				case 1:
					// Endere�o do WebService da LocaWeb
					$correiosWSLocaWeb = "http://comercio.locaweb.com.br/correios/frete.asmx?WSDL";
					
					// Define os valores para o c�lculo do frete
					$int_cepOrigem = $frompcode;
					$int_cepDestino = $topcode;
					$int_pesoFrete = number_format($sweight, 2, ',', '.');
					$int_volumeFrete = "";
					$int_codigoFrete = "";
					
					if ($shipping_method == 41106) // PAC
						$int_codigoFrete = "41025";
					else if ($shipping_method == 40010) // SEDEX	
						$int_codigoFrete = "40096";
					else if ($shipping_method == 81019) // E-SEDEX	
						$int_codigoFrete = "81019";
					
					
					
					// Inicializa o cliente SOAP
					try {
					$soap = @new SoapClient($correiosWSLocaWeb, array(
					        'trace' => true,
					        'exceptions' => true,
					        'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
					        'connection_timeout' => 1000
					));
					}
					catch (SoapFault $e) {
						$error->setErrorMessage($this->getConfigData('urlerror'));
						$result->append($error);
						return $result;
					}
					
					// Postagem dos par�metros
					$parms = new Parameters();
					$parms->cepOrigem = utf8_encode($int_cepOrigem);
					$parms->cepDestino = utf8_encode($int_cepDestino);
					$parms->peso = utf8_encode($int_pesoFrete);
					$parms->volume = utf8_encode($int_volumeFrete);
					$parms->codigo = utf8_encode($int_codigoFrete);
					
					// Resgata o valor calculado
					$resposta = $soap->Correios($parms);
					// Exibe os dados de retorno 
					
					
					
					if (substr( utf8_decode($resposta->CorreiosResult), 0,1 ) == 'S') {
						//$error->setErrorMessage($this->getConfigData('zipcodeerror'));
						//$result->append($error);
						return $result;
						
					}
					
					$shippingPrice =  str_replace(',', '.', utf8_decode($resposta->CorreiosResult));	
					
					break;
					
				case 0:
					
					$url = "http://www.correios.com.br/encomendas/precos/calculo.cfm?resposta=xml" .
			
					"&servico=" . $shipping_method .
					"&cepOrigem=" . $frompcode .
					"&cepDestino=" . $topcode . 
					"&peso=" . $sweight;
					
					if(!(@$carrega = file($url))){
						//The URL is incorrect
						$error->setErrorMessage($this->getConfigData('urlerror'));
						$result->append($error);
						return $result;
					}
							
					$conteudo = trim(str_replace(array("\n", chr(13)), "", implode($carrega, "")));
		
					if(strlen($conteudo) <1){
						//The URL is incorrect
						$error->setErrorMessage($this->getConfigData('urlerror'));
						$result->append($error);
						return $result;
					}
		
					preg_match_all("/<servico>(.+)<\/servico>/", $conteudo, $xml_servico);
					preg_match_all("/<uf_origem>(.+)<\/uf_origem>/", $conteudo, $uf_origem);
					preg_match_all("/<local_origem>(.+)<\/local_origem>/", $conteudo, $local_origem);
					preg_match_all("/<cep_origem>(.+)<\/cep_origem>/", $conteudo, $cep_origem);
		
					preg_match_all("/<uf_destino>(.+)<\/uf_destino>/", $conteudo, $uf_destino);
					preg_match_all("/<local_destino>(.+)<\/local_destino>/", $conteudo, $local_destino);
					preg_match_all("/<cep_destino>(.+)<\/cep_destino>/", $conteudo, $cep_destino);
		
					preg_match_all("/<peso>(.+)<\/peso>/", $conteudo, $peso);
					preg_match_all("/<preco_postal>(.+)<\/preco_postal>/", $conteudo, $preco_postal);
				
					$sedex = array(
						"servico" => $xml_servico[1][0],
						"valor" => floatval($preco_postal[1][0])
					);
		
					$err_msg = "OK";	
					
					if(trim($err_msg) == "OK"){
						$shippingPrice = floatval($preco_postal[1][0]);
					}else{
						//Invalid Zip Code
						$error->setErrorMessage($this->getConfigData('zipcodeerror'));
						$result->append($error);
						return $result;
					}
					
					break;
				default:
					//URL method undefined
					$error->setErrorMessage($this->getConfigData('urlerror'));
					$result->append($error);
					return $result;
			}
			
			if($shippingPrice <= 0){
				continue;
			}
			
			$method = Mage::getModel('shipping/rate_result_method');
			
			$method->setCarrier($this->_code);
	        $method->setCarrierTitle($this->getConfigData('name'));
			
	   	    $method->setMethod($shipping_method);
        	
			if ($this->getConfigFlag('prazo_entrega')){
				$method->setMethodTitle(sprintf($this->getConfigData('msgprazo'), $shipping_values[0], $shipping_values[1]));				
			}else{
				$method->setMethodTitle($shipping_values[0]);
			}
			
			$method->setPrice($shippingPrice + $this->getConfigData('handling_fee'));
    	    
			$method->setCost($shippingPrice);

	        $result->append($method);
		}
		
		$this->_result = $result;

		//mail("acruz@almqconsultoria.com", "Resultado Consulta Frete", serialize($result));
				
		$this->_updateFreeMethodQuote($request);
		
		return $this->_result;
    }
    
        /**
	 * Get Tracking Info
	 *
	 * @param mixed $tracking
	 * @return mixed
	 */
	public function getTrackingInfo($tracking) {
		$result = $this->getTracking($tracking);
		if ($result instanceof Mage_Shipping_Model_Tracking_Result){
			if ($trackings = $result->getAllTrackings()) {
				return $trackings[0];
			}
		} elseif (is_string($result) && !empty($result)) {
			return $result;
		}

		return false;
	}

	/**
	 * Get Tracking
	 *
	 * @param array $trackings
	 * @return Mage_Shipping_Model_Tracking_Result
	 */
	public function getTracking($trackings) {
		$this->_result = Mage::getModel('shipping/tracking_result');
		foreach ((array) $trackings as $code) {
			$this->_getTracking($code);
		}

		return $this->_result;
	}

	/**
	 * Protected Get Tracking, opens the request to Correios
	 *
	 * @param string $code
	 * @return boolean
	 */
	protected function _getTracking($code) {
		$error = Mage::getModel('shipping/tracking_result_error');
                $matches = array();
                $error->setTracking($code);
                $error->setCarrier('correios');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
 
                $content = mb_convert_encoding(file_get_contents("http://websro.correios.com.br/sro_bin/txect01$.Inexistente?P_LINGUA=001&P_TIPO=002&P_COD_LIS=" . $code), 'UTF-8');
                $content = preg_match('/<table([#a-zA-Z 0-9-=:;\'"]+)>(.*)<\/table>/mis', $content, $matches);
                $content = end($matches);
                $content = preg_replace('/(\n)?(\r\n)?<colgroup([#a-zA-Z 0-9-=:;\'"]+)><colgroup([#a-zA-Z 0-9-=:;\'"]+)><colgroup([#a-zA-Z 0-9-=:;\'"]+)>/mis', '', $content);
                $content = preg_replace('/<font([#a-zA-Z 0-9-=:;\'"]+)>/mis',   '', $content);
                $content = preg_replace('/<\/font>/mis',                                                '', $content);
                $content = preg_replace('/<\/font>/mis',                                                '', $content);
                $content = preg_replace('/<b>/mis',                                                             '', $content);
                $content = preg_replace('/<\/b>/mis',                                                   '', $content);
                $content = preg_replace('/(\n\n)/mis',                                                  "\n", $content);
echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<title></title>
	<style type="text/css">
	/* -----------------------------------------------------------------------


	 Blueprint CSS Framework 1.0.1
	 http://blueprintcss.org

	   * Copyright (c) 2007-Present. See LICENSE for more info.
	   * See README for instructions on how to use Blueprint.
	   * For credits and origins, see AUTHORS.
	   * This is a compressed file. See the sources in the 'src' directory.

	----------------------------------------------------------------------- */

	/* reset.css */
	html {margin:0;padding:0;border:0;}
	body, div, span, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, code, del, dfn, em, img, q, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, dialog, figure, footer, header, hgroup, nav, section {margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline;}
	article, aside, details, figcaption, figure, dialog, footer, header, hgroup, menu, nav, section {display:block;}
	body {line-height:1.5;background:white;}
	table {border-collapse:separate;border-spacing:0;border: 1px solid #f2f2f2;}
	caption, th, td {text-align:left;font-weight:normal;float:none !important;}
	table, th, td {vertical-align:middle;}
	blockquote:before, blockquote:after, q:before, q:after {content:'';}
	blockquote, q {quotes:"" "";}
	a img {border:none;}
	:focus {outline:0;}

	/* typography.css */
	html {font-size:100.01%;}
	body {font-size:75%;color:#222;background:#fff;font-family:"Helvetica Neue", Arial, Helvetica, sans-serif;}
	h1, h2, h3, h4, h5, h6 {font-weight:normal;color:#111;}
	h1 {font-size:3em;line-height:1;margin-bottom:0.5em;}
	h2 {font-size:2em;margin-bottom:0.75em;}
	h3 {font-size:1.5em;line-height:1;margin-bottom:1em;}
	h4 {font-size:1.2em;line-height:1.25;margin-bottom:1.25em;}
	h5 {font-size:1em;font-weight:bold;margin-bottom:1.5em;}
	h6 {font-size:1em;font-weight:bold;}
	h1 img, h2 img, h3 img, h4 img, h5 img, h6 img {margin:0;}
	p {margin:0 0 1.5em;}
	.left {float:left !important;}
	p .left {margin:1.5em 1.5em 1.5em 0;padding:0;}
	.right {float:right !important;}
	p .right {margin:1.5em 0 1.5em 1.5em;padding:0;}
	a:focus, a:hover {color:#09f;}
	a {color:#06c;text-decoration:underline;}
	blockquote {margin:1.5em;color:#666;font-style:italic;}
	strong, dfn {font-weight:bold;}
	em, dfn {font-style:italic;}
	sup, sub {line-height:0;}
	abbr, acronym {border-bottom:1px dotted #666;}
	address {margin:0 0 1.5em;font-style:italic;}
	del {color:#666;}
	pre {margin:1.5em 0;white-space:pre;}
	pre, code, tt {font:1em 'andale mono', 'lucida console', monospace;line-height:1.5;}
	li ul, li ol {margin:0;}
	ul, ol {margin:0 1.5em 1.5em 0;padding-left:1.5em;}
	ul {list-style-type:disc;}
	ol {list-style-type:decimal;}
	dl {margin:0 0 1.5em 0;}
	dl dt {font-weight:bold;}
	dd {margin-left:1.5em;}
	table {margin-bottom:1.4em;width:100%;}
	th {font-weight:bold;}
	thead th {background:#c3d9ff;}
	th, td, caption {padding:4px 10px 4px 5px;}
	tbody tr:nth-child(even) td, tbody tr.even td {background:#eee;}
	tfoot {font-style:italic;}
	caption {background:#eee;}
	.small {font-size:.8em;margin-bottom:1.875em;line-height:1.875em;}
	.large {font-size:1.2em;line-height:2.5em;margin-bottom:1.25em;}
	.hide {display:none;}
	.quiet {color:#666;}
	.loud {color:#000;}
	.highlight {background:#ff0;}
	.added {background:#060;color:#fff;}
	.removed {background:#900;color:#fff;}
	.first {margin-left:0;padding-left:0;}
	.last {margin-right:0;padding-right:0;}
	.top {margin-top:0;padding-top:0;}
	.bottom {margin-bottom:0;padding-bottom:0;}

	/* forms.css */
	label {font-weight:bold;}
	fieldset {padding:0 1.4em 1.4em 1.4em;margin:0 0 1.5em 0;border:1px solid #ccc;}
	legend {font-weight:bold;font-size:1.2em;margin-top:-0.2em;margin-bottom:1em;}
	fieldset, #IE8#HACK {padding-top:1.4em;}
	legend, #IE8#HACK {margin-top:0;margin-bottom:0;}
	input[type=text], input[type=password], input[type=url], input[type=email], input.text, input.title, textarea {background-color:#fff;border:1px solid #bbb;color:#000;}
	input[type=text]:focus, input[type=password]:focus, input[type=url]:focus, input[type=email]:focus, input.text:focus, input.title:focus, textarea:focus {border-color:#666;}
	select {background-color:#fff;border-width:1px;border-style:solid;}
	input[type=text], input[type=password], input[type=url], input[type=email], input.text, input.title, textarea, select {margin:0.5em 0;}
	input.text, input.title {width:300px;padding:5px;}
	input.title {font-size:1.5em;}
	textarea {width:390px;height:250px;padding:5px;}
	form.inline {line-height:3;}
	form.inline p {margin-bottom:0;}
	.error, .alert, .notice, .success, .info {padding:0.8em;margin-bottom:1em;border:2px solid #ddd;}
	.error, .alert {background:#fbe3e4;color:#8a1f11;border-color:#fbc2c4;}
	.notice {background:#fff6bf;color:#514721;border-color:#ffd324;}
	.success {background:#e6efc2;color:#264409;border-color:#c6d880;}
	.info {background:#d5edf8;color:#205791;border-color:#92cae4;}
	.error a, .alert a {color:#8a1f11;}
	.notice a {color:#514721;}
	.success a {color:#264409;}
	.info a {color:#205791;}

	/* grid.css */
	.container {width:950px;margin:0 auto;}
	.showgrid {background:url(src/grid.png);}
	.column, .span-1, .span-2, .span-3, .span-4, .span-5, .span-6, .span-7, .span-8, .span-9, .span-10, .span-11, .span-12, .span-13, .span-14, .span-15, .span-16, .span-17, .span-18, .span-19, .span-20, .span-21, .span-22, .span-23, .span-24 {float:left;margin-right:10px;}
	.last {margin-right:0;}
	.span-1 {width:30px;}
	.span-2 {width:70px;}
	.span-3 {width:110px;}
	.span-4 {width:150px;}
	.span-5 {width:190px;}
	.span-6 {width:230px;}
	.span-7 {width:270px;}
	.span-8 {width:310px;}
	.span-9 {width:350px;}
	.span-10 {width:390px;}
	.span-11 {width:430px;}
	.span-12 {width:470px;}
	.span-13 {width:510px;}
	.span-14 {width:550px;}
	.span-15 {width:590px;}
	.span-16 {width:630px;}
	.span-17 {width:670px;}
	.span-18 {width:710px;}
	.span-19 {width:750px;}
	.span-20 {width:790px;}
	.span-21 {width:830px;}
	.span-22 {width:870px;}
	.span-23 {width:910px;}
	.span-24 {width:950px;margin-right:0;}
	input.span-1, textarea.span-1, input.span-2, textarea.span-2, input.span-3, textarea.span-3, input.span-4, textarea.span-4, input.span-5, textarea.span-5, input.span-6, textarea.span-6, input.span-7, textarea.span-7, input.span-8, textarea.span-8, input.span-9, textarea.span-9, input.span-10, textarea.span-10, input.span-11, textarea.span-11, input.span-12, textarea.span-12, input.span-13, textarea.span-13, input.span-14, textarea.span-14, input.span-15, textarea.span-15, input.span-16, textarea.span-16, input.span-17, textarea.span-17, input.span-18, textarea.span-18, input.span-19, textarea.span-19, input.span-20, textarea.span-20, input.span-21, textarea.span-21, input.span-22, textarea.span-22, input.span-23, textarea.span-23, input.span-24, textarea.span-24 {border-left-width:1px;border-right-width:1px;padding-left:5px;padding-right:5px;}
	input.span-1, textarea.span-1 {width:18px;}
	input.span-2, textarea.span-2 {width:58px;}
	input.span-3, textarea.span-3 {width:98px;}
	input.span-4, textarea.span-4 {width:138px;}
	input.span-5, textarea.span-5 {width:178px;}
	input.span-6, textarea.span-6 {width:218px;}
	input.span-7, textarea.span-7 {width:258px;}
	input.span-8, textarea.span-8 {width:298px;}
	input.span-9, textarea.span-9 {width:338px;}
	input.span-10, textarea.span-10 {width:378px;}
	input.span-11, textarea.span-11 {width:418px;}
	input.span-12, textarea.span-12 {width:458px;}
	input.span-13, textarea.span-13 {width:498px;}
	input.span-14, textarea.span-14 {width:538px;}
	input.span-15, textarea.span-15 {width:578px;}
	input.span-16, textarea.span-16 {width:618px;}
	input.span-17, textarea.span-17 {width:658px;}
	input.span-18, textarea.span-18 {width:698px;}
	input.span-19, textarea.span-19 {width:738px;}
	input.span-20, textarea.span-20 {width:778px;}
	input.span-21, textarea.span-21 {width:818px;}
	input.span-22, textarea.span-22 {width:858px;}
	input.span-23, textarea.span-23 {width:898px;}
	input.span-24, textarea.span-24 {width:938px;}
	.append-1 {padding-right:40px;}
	.append-2 {padding-right:80px;}
	.append-3 {padding-right:120px;}
	.append-4 {padding-right:160px;}
	.append-5 {padding-right:200px;}
	.append-6 {padding-right:240px;}
	.append-7 {padding-right:280px;}
	.append-8 {padding-right:320px;}
	.append-9 {padding-right:360px;}
	.append-10 {padding-right:400px;}
	.append-11 {padding-right:440px;}
	.append-12 {padding-right:480px;}
	.append-13 {padding-right:520px;}
	.append-14 {padding-right:560px;}
	.append-15 {padding-right:600px;}
	.append-16 {padding-right:640px;}
	.append-17 {padding-right:680px;}
	.append-18 {padding-right:720px;}
	.append-19 {padding-right:760px;}
	.append-20 {padding-right:800px;}
	.append-21 {padding-right:840px;}
	.append-22 {padding-right:880px;}
	.append-23 {padding-right:920px;}
	.prepend-1 {padding-left:40px;}
	.prepend-2 {padding-left:80px;}
	.prepend-3 {padding-left:120px;}
	.prepend-4 {padding-left:160px;}
	.prepend-5 {padding-left:200px;}
	.prepend-6 {padding-left:240px;}
	.prepend-7 {padding-left:280px;}
	.prepend-8 {padding-left:320px;}
	.prepend-9 {padding-left:360px;}
	.prepend-10 {padding-left:400px;}
	.prepend-11 {padding-left:440px;}
	.prepend-12 {padding-left:480px;}
	.prepend-13 {padding-left:520px;}
	.prepend-14 {padding-left:560px;}
	.prepend-15 {padding-left:600px;}
	.prepend-16 {padding-left:640px;}
	.prepend-17 {padding-left:680px;}
	.prepend-18 {padding-left:720px;}
	.prepend-19 {padding-left:760px;}
	.prepend-20 {padding-left:800px;}
	.prepend-21 {padding-left:840px;}
	.prepend-22 {padding-left:880px;}
	.prepend-23 {padding-left:920px;}
	.border {padding-right:4px;margin-right:5px;border-right:1px solid #ddd;}
	.colborder {padding-right:24px;margin-right:25px;border-right:1px solid #ddd;}
	.pull-1 {margin-left:-40px;}
	.pull-2 {margin-left:-80px;}
	.pull-3 {margin-left:-120px;}
	.pull-4 {margin-left:-160px;}
	.pull-5 {margin-left:-200px;}
	.pull-6 {margin-left:-240px;}
	.pull-7 {margin-left:-280px;}
	.pull-8 {margin-left:-320px;}
	.pull-9 {margin-left:-360px;}
	.pull-10 {margin-left:-400px;}
	.pull-11 {margin-left:-440px;}
	.pull-12 {margin-left:-480px;}
	.pull-13 {margin-left:-520px;}
	.pull-14 {margin-left:-560px;}
	.pull-15 {margin-left:-600px;}
	.pull-16 {margin-left:-640px;}
	.pull-17 {margin-left:-680px;}
	.pull-18 {margin-left:-720px;}
	.pull-19 {margin-left:-760px;}
	.pull-20 {margin-left:-800px;}
	.pull-21 {margin-left:-840px;}
	.pull-22 {margin-left:-880px;}
	.pull-23 {margin-left:-920px;}
	.pull-24 {margin-left:-960px;}
	.pull-1, .pull-2, .pull-3, .pull-4, .pull-5, .pull-6, .pull-7, .pull-8, .pull-9, .pull-10, .pull-11, .pull-12, .pull-13, .pull-14, .pull-15, .pull-16, .pull-17, .pull-18, .pull-19, .pull-20, .pull-21, .pull-22, .pull-23, .pull-24 {float:left;position:relative;}
	.push-1 {margin:0 -40px 1.5em 40px;}
	.push-2 {margin:0 -80px 1.5em 80px;}
	.push-3 {margin:0 -120px 1.5em 120px;}
	.push-4 {margin:0 -160px 1.5em 160px;}
	.push-5 {margin:0 -200px 1.5em 200px;}
	.push-6 {margin:0 -240px 1.5em 240px;}
	.push-7 {margin:0 -280px 1.5em 280px;}
	.push-8 {margin:0 -320px 1.5em 320px;}
	.push-9 {margin:0 -360px 1.5em 360px;}
	.push-10 {margin:0 -400px 1.5em 400px;}
	.push-11 {margin:0 -440px 1.5em 440px;}
	.push-12 {margin:0 -480px 1.5em 480px;}
	.push-13 {margin:0 -520px 1.5em 520px;}
	.push-14 {margin:0 -560px 1.5em 560px;}
	.push-15 {margin:0 -600px 1.5em 600px;}
	.push-16 {margin:0 -640px 1.5em 640px;}
	.push-17 {margin:0 -680px 1.5em 680px;}
	.push-18 {margin:0 -720px 1.5em 720px;}
	.push-19 {margin:0 -760px 1.5em 760px;}
	.push-20 {margin:0 -800px 1.5em 800px;}
	.push-21 {margin:0 -840px 1.5em 840px;}
	.push-22 {margin:0 -880px 1.5em 880px;}
	.push-23 {margin:0 -920px 1.5em 920px;}
	.push-24 {margin:0 -960px 1.5em 960px;}
	.push-1, .push-2, .push-3, .push-4, .push-5, .push-6, .push-7, .push-8, .push-9, .push-10, .push-11, .push-12, .push-13, .push-14, .push-15, .push-16, .push-17, .push-18, .push-19, .push-20, .push-21, .push-22, .push-23, .push-24 {float:left;position:relative;}
	div.prepend-top, .prepend-top {margin-top:1.5em;}
	div.append-bottom, .append-bottom {margin-bottom:1.5em;}
	.box {padding:1.5em;margin-bottom:1.5em;background:#eee;}
	hr {background:#ddd;color:#ddd;clear:both;float:none;width:100%;height:1px;margin:0 0 17px;border:none;}
	hr.space {background:#fff;color:#fff;visibility:hidden;}
	.clearfix:after, .container:after {content:"\0020";display:block;height:0;clear:both;visibility:hidden;overflow:hidden;}
	.clearfix, .container {display:block;}
	.clear {clear:both;}
	#logo {
		height: 89px;
		width: 90%;
		margin: 0px auto 0px auto;
		clear: both;
		background: url("http://www.farmadelivery.com.br/skin/frontend/FarmaDelivery/nova_farma/images/bg_header.png") no-repeat scroll left top #F2F2F2;
	}
	</style>
</head>
<body>
	<div id="logo">
	</div>
	<div style="width: 90%; margin: 0px auto;">
	<table style="padding: 0xp; margin: 0px; border-bottom: none;">
      <tr><td>C&oacute;digo de rastreamento: $code</td></tr>
    </table>
	    <table>
		    $content
		</table>
	</div>
</body>
</html>
EOF;
                
                exit;
	}

	public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('title'));
    }

}
