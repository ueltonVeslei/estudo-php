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

		if(!ereg("^[0-9]{8}$", $topcode))
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
						$shipping_methods["40010"] = array ("Sedex", "3");
						break;
					case 2:
						$shipping_methods["40215"] = array ("Sedex 10", "1");
						break;
					case 3:
						$shipping_methods["40290"] = array ("Sedex HOJE", "1");
						break;
					case 4:
						$shipping_methods["81019"] = array ("E-Sedex", "3");
						break;
			}
		}
		
		foreach($shipping_methods as $shipping_method => $shipping_values){
        
            //Define URL method
			switch ($this->getConfigData('urlmethod')){
				
				case 1:
					// Endereço do WebService da LocaWeb
					$correiosWSLocaWeb = "http://comercio.locaweb.com.br/correios/frete.asmx?WSDL";
					
					// Define os valores para o cálculo do frete
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
					
					// Postagem dos parâmetros
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
						$error->setErrorMessage($this->getConfigData('zipcodeerror'));
						$result->append($error);
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
		
		$this->_updateFreeMethodQuote($request);
		
		return $this->_result;
    }

	public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('title'));
    }

}
