<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Shipping (Frete)
 * @package    Av5_Correiospro
 * @copyright  Copyright (c) 2013 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Av5_Correiospro_Model_Webservice
 *
 * @category   Shipping
 * @package    Av5_Correiospro
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */

class Av5_Correiospro_Model_Webservice extends Varien_Object {
	
	/**
	 * Propriedades da classe 
	 */
	protected $_code				= "av5_correiospro";
	protected $_from				= NULL; // CEP de origem
	protected $_wsUrl				= NULL; // URL do webservice
	protected $_wsTimeout			= NULL; // Timeout do webservice
	protected $_login				= NULL; // Login do Webservice (Contrato)
	protected $_password			= NULL; // Senha do webservice (Contrato)
	protected $_defHeight			= NULL; // Altura padrão de pacotes
	protected $_defWidth			= NULL; // Comprimento padrão de pacotes
	protected $_defDepth			= NULL; // Largura padrão de pacotes
	protected $_weightType			= NULL; // Medida de peso padrão
	protected $_maxWeight			= NULL; // Peso máximo permitido
	protected $_postingMethods		= NULL; // Serviços de postagem
	protected $_pacCodes			= NULL; // Serviços PAC
	protected $_ownerHands			= NULL; // Entrega em mãos próprias
	protected $_receivedWarning		= NULL; // Aviso de recebimento
	protected $_declaredValue		= NULL; // Valor declarado
	protected $_initiated			= false; // Controla se as variáveis já foram inicializadas
	protected $_allowedErrors		= array("009","010","011"); // Códigos de erro permitidos, controle de área de risco
	protected $_client				= NULL; // SOAPCLIENT
	
	public function getRates(Mage_Shipping_Model_Rate_Request $request) {
		$this->_init();
		$weight = $request->getFixedPackageWeight();
		$toZip = Mage::helper('av5_correiospro')->_formatZip($request->getDestPostcode());
		
		$postingMethods = $request->getPostingMethods();
		
		if ($postingMethods) {
			$this->_postingMethods = $postingMethods;
			if (!is_array($this->_postingMethods)) {
				$this->_postingMethods = explode(',', $this->_postingMethods);
			}
		}
		$data = $this->processXml($weight, $toZip, $request->getPackageValue());
		
		return $data;
	}
	
	protected function processXml($weight, $toZip, $packageValue) {
	    $rates = [];
	    foreach ($this->_postingMethods as $postingMethod) {
            $params = array(
                "nCdEmpresa" 			=> $this->_login,
                "sDsSenha" 				=> $this->_password,
                "nCdFormato"			=> "1",
                "nCdServico"			=> $postingMethod,
                "nVlComprimento"		=> $this->_defWidth,
                "nVlAltura"				=> $this->_defHeight,
                "nVlLargura"			=> $this->_defDepth,
                "nVlDiametro"			=> "20",
                "sCepOrigem"			=> $this->_from,
                "sCdMaoPropria"			=> $this->_ownerHands,
                "sCdAvisoRecebimento"	=> $this->_receivedWarning,
                "nVlPeso"				=> $weight,
                "sCepDestino"			=> $toZip
            );

            if ($this->getConfigData('acobrar_code') == $postingMethod) { // SEDEX A COBRAR
                $params["nVlValorDeclarado"] = number_format($packageValue, 2, '.', '');
            } else {
                if ($this->_declaredValue > 0) {
                    $this->_declaredValue = number_format($packageValue, 2, '.', '');
                }
                $params["nVlValorDeclarado"] = $this->_declaredValue;
            }

            try {
                $xml = $this->getCurlResponse($params);

                if (!$xml){
                    throw new Exception("No XML returned [" . __LINE__ . "]");
                    Mage::helper('av5_correiospro')->log("AV5_Correios Erro: Correios fora do ar.");
                }

                $cServico = $xml->cServico;
                /*if (!is_array($xml->cServico)) {
                    $cServico = array($xml->cServico);
                }*/

                if(count($cServico) <= 0){
                    throw new Exception("No tag cServico in Correios XML [" . __LINE__ . "]");
                }

                if ($cServico) {
                    $newRate = $this->updateOffline($cServico, $weight, $toZip, $packageValue);
                    $rates = array_merge($rates, $newRate);
                }

            } catch (Exception $e) {
                //URL Error
                Mage::helper('av5_correiospro')->log("AV5_Correios Erro de URL: " . $e->getMessage() . " - " . $this->_wsUrl);
                return false;
            };
        }

		return $rates;
	}
	
	protected function updateOffline($xml, $weight, $toZip, $pkgValue){
		$newdata = array();
		$mustDelete = false;
		foreach($xml as $servico) {
			if ($servico->Erro == "0" || in_array($servico->Erro, $this->_allowedErrors)) {
				try {
					$data = array();
					if ($servico->Codigo == $this->getConfigData('acobrar_code')) {
						$data['valor'] = str_replace(",",".",$servico->ValorSemAdicionais);
					} else {
						$data['valor'] = str_replace(",",".",$servico->Valor);
					}
					
					$data['valor_lq'] = str_replace(",",".",$servico->ValorSemAdicionais);
					$data['valor_ar'] = str_replace(",",".",$servico->ValorAvisoRecebimento);
					$data['valor_mp'] = str_replace(",",".",$servico->ValorMaoPropria);
					$data['valor_sg'] = str_replace(",",".",$servico->ValorValorDeclarado);
					$data['valor_pct'] = $pkgValue;
					
					$data['prazo'] = (string)$servico->PrazoEntrega;
					$data['lastupdate'] = date('Y-m-d H:i:s');
					
					$data['areas_risco'] = "";
					if (in_array($servico->Erro,$this->_allowedErrors)){
						$data['areas_risco'] = (string)$servico->MsgErro;
					}

                    $method = (string)$servico->Codigo;
                    if (strlen($method) == 4) {
                        $method = (string)'0' . $method;
                    }

					if(in_array($method,$this->_pacCodes) && $weight < 0.5) {
						$weight = '0.5';
					}
					
					if ($weight > 0.5) {
						$weight = ceil($weight);
					}
					
					if ($weight < 0.3) {
						$weight = '0.3';
					}
					
					$data['servico'] = $method;
					$data['zipcode'] = $toZip;
					$data['weight'] = $weight;
					Mage::getModel('av5_correiospro/price')->update($data);
					$newdata[] = $data;
				} catch (Exception $e) {
					Mage::helper('av5_correiospro')->log("AV5_Correios Erro: " . $e->getMessage() . " - CEP: " . $toZip . ' para ' . $servico->Codigo . ':' . Mage::helper('av5_correiospro')->getServiceName($servico->Codigo));
				}
			} else {
				Mage::helper('av5_correiospro')->log('ERRO >> SERVICO: ' . $servico->Codigo . ' - CEP: ' .$toZip . ' - MSG: ' . (string)$servico->MsgErro);
				break;
			}
		}
		
		return $newdata;
	}
	
	protected function getClient() {
		if (!$this->_client) {
			$this->_client = new SoapClient($this->_wsUrl, array('connection_timeout' => $this->_wsTimeout, 'cache_wsdl' => WSDL_CACHE_DISK));
		}
		
		return $this->_client;
	}

	protected function getCurlResponse($params) {
	    $paramsValues = [];
	    foreach ($params as $param => $value) {
	        $paramsValues[] = $param . '=' . $value;
        }
        $apiUrl = $this->_wsUrl . '?StrRetorno=xml&' . implode('&', $paramsValues);

        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL, $apiUrl);
        curl_setopt($curl_session, CURLOPT_FAILONERROR, true);
        curl_setopt($curl_session, CURLOPT_CONNECTTIMEOUT, $this->_wsTimeout);
        curl_setopt($curl_session, CURLOPT_TIMEOUT, $this->_wsTimeout);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl_session);
        curl_close($curl_session);

        $response = simplexml_load_string($response);

        return $response;
    }
	
	/**
	 * Inicializa as propriedades da classe
	 */
	protected function _init() {
		if (!$this->_initiated) {
			$this->_wsUrl = str_replace('\\?', '?', $this->getConfigData('webservices/price/url'));
			$this->_login = $this->getConfigData('login');
			$this->_password = $this->getConfigData('password');
			$this->_defHeight = $this->getConfigData('default_height');
			$this->_defWidth = $this->getConfigData('default_width');
			$this->_defDepth = $this->getConfigData('default_depth');
			$this->_weightType = $this->getConfigData('weight_type');
			$this->_maxWeight = $this->_fixWeight($this->getConfigData('max_weight'));
			$this->_updateFrequency = $this->getConfigData('update_frequency');
			$this->_postingMethods = $this->getConfigData('posting_methods');
			$this->_pacCodes = explode(",", $this->getConfigData('pac_codes'));
			$this->_deleteCodes = explode(",",$this->getConfigData('delete_codes'));
			$this->_limitRecords = $this->getConfigData('limit_records');
			$this->_ownerHands = ($this->getConfigData('owner_hands')) ? 'S' : 'N';
			$this->_receivedWarning = ($this->getConfigData('received_warning')) ? 'S' : 'N';
			$this->_declaredValue = $this->getConfigData('declared_value');
			$this->_from = Mage::helper('av5_correiospro')->_formatZip(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
			$this->_initiated = true;
		}
	}
	
	/**
	 * Recupera configurações do módulo
	 * @param string $field
	 * @return boolean, mixed, string, NULL
	 */
	public function getConfigData($field)
	{
		if (empty($this->_code)) {
			return false;
		}
		$path = 'carriers/'.$this->_code.'/'.$field;
		return Mage::getStoreConfig($path, $this->getStore());
	}
	
	/**
	 * Corrige o peso informado com base na medida de peso configurada
	 * @param string|int|float $weight
	 * @return double
	 */
	protected function _fixWeight($weight) {
		$result = $weight;
		if ($this->_weightType == 'gr') {
			$result = number_format($weight/1000, 2, '.', '');
		}
		 
		return $result;
	}
	
}