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
 * Av5_Correiospro_Model_Carrier_CorreiosMethod
 *
 * @category   Shipping
 * @package    Av5_Correiospro
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */

class Av5_Correiospro_Model_Carrier_CorreiosMethod extends Mage_Shipping_Model_Carrier_Abstract {

    /**
     * Código de operação do módulo
     * @var string
     */
	protected $_code 				= 'av5_correiospro';

	/**
	 * Variáveis de controle de CEPs
	 * @var string
	 */
    protected $_from				= NULL; // CEP de origem
    protected $_to					= NULL; // CEP de destino

    /**
     * Controle de retorno de operações
     * @var Mage_Shipping_Model_Rate_Result / Mage_Shipping_Model_Tracking_Result
     */
    protected $_result				= NULL;

    /**
     * Controles do pacote
     */
    protected $_value				= NULL; // Valor do pedido
    protected $_weight				= NULL; // Peso total do pedido
    protected $_freeWeight			= NULL; // Peso total do frete grátis
    protected $_cubic				= NULL; // Peso cúbico total - PAC

    /**
     * Configurações diversas
     */
    protected $_maxOrderValue		= NULL; // Valor máximo do Pedido
    protected $_minOrderValue		= NULL; // Valor mínimo do Pedido
    protected $_maxWeight			= NULL; // Peso máximo permitido
    protected $_weightType			= NULL; // Peso máximo permitido
    protected $_defHeight			= NULL; // Altura padrão de pacotes
    protected $_defWidth			= NULL; // Comprimento padrão de pacotes
    protected $_defDepth			= NULL; // Largura padrão de pacotes
    protected $_postingMethods		= NULL; // Métodos de envio disponíveis
    protected $_title				= NULL; // Título do método de envio
    protected $_handlingFee			= NULL; // Taxa de envio
    protected $_request				= NULL; // Controle de requisição
    protected $_pacCodes			= NULL; // Serviços PAC
    protected $_operation			= NULL; // Modalidade de operação do módulo
    protected $_packPartitioning	= NULL; // Particionamento em pacotes para ultrapassar o limite de peso enviado


    /**
     * Verifica se o país está dentro da área atendida
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return boolean
     */
    protected function _checkCountry() {
    	$from = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());
    	$to = $this->_request->getDestCountryId();
    	if ($from != "BR" || $to != "BR"){
    		Mage::helper('av5_correiospro')->log('Fora da área de atendimento');
    		return false;
    	}

    	return true;
    }

    /**
     * Recupera, formata e verifica se os CEPs de origem e destino estão
     * dentro do padrão correto
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return boolean
     */
    protected function _checkZipCode() {
    	$this->_from = Mage::helper('av5_correiospro')->_formatZip(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
    	$this->_to = Mage::helper('av5_correiospro')->_formatZip($this->_request->getDestPostcode());

    	if(!$this->_from){
    		Mage::helper('av5_correiospro')->log('Erro com CEP de origem');
    		$this->_throwError('correioserror', 'CEP de Origem Inválido', __LINE__);
    		return false;
    	}

    	if(!$this->_request->getDestPostcode()){
    		Mage::helper('av5_correiospro')->log('CEP de destino vazio');
    		$this->_throwError('zipemptyerror', 'CEP Vazio', __LINE__);
    		return false;
    	}

    	if(!$this->_to){
    		Mage::helper('av5_correiospro')->log('Erro com CEP de destino');
    		$this->_throwError('zipcodeerror', 'CEP Inválido', __LINE__);
    		return false;
    	}

    	return true;
    }

    protected function _init(Mage_Shipping_Model_Rate_Request $request){
    	if (!$this->isActive()) {
    		Mage::helper('av5_correiospro')->log('Módulo Desabilitado');
    		return false;
    	}

    	$this->_request = $request;
    	$this->_title = $this->getConfigData('title');
    	$this->_weightType = $this->getConfigData('weight_type');
    	$this->_defHeight = $this->getConfigData('default_height');
    	$this->_defWidth = $this->getConfigData('default_width');
    	$this->_defDepth = $this->getConfigData('default_depth');
    	$this->_maxWeight = $this->getConfigData('maxweight');
    	$this->_maxOrderValue = $this->getConfigData('max_order_value');
    	$this->_minOrderValue = $this->getConfigData('min_order_value');
    	$this->_postingMethods = explode(",", $this->getConfigData('posting_methods'));
    	$this->_handlingFee = $this->getConfigData('handling_fee');
    	$this->_showDelivery = $this->getConfigData('show_delivery');
    	$this->_addDeliveryDays = $this->getConfigData('add_delivery_days');
    	$this->_declaredValue = $this->getConfigData('declared_value');
    	$this->_receivedWarning = $this->getConfigData('received_warning');
    	$this->_ownerHands = $this->getConfigData('owner_hands');
    	$this->_pacCodes = explode(",", $this->getConfigData('pac_codes'));
    	$this->_operation = $this->getConfigData('mode');
    	$this->_packPartitioning = $this->getConfigData('pack_partition');

    	$this->_result = Mage::getModel('shipping/rate_result');
    	$this->_value = $request->getBaseCurrency()->convert($request->getPackageValue(), $request->getPackageCurrency());
    	$this->_weight = $this->_fixWeight($request->getPackageWeight());
    	$this->_freeWeight = $this->_fixWeight($request->getFreeMethodWeight());

    	return true;
    }

    /**
     * Checa se o valor do pedido está entre as faixas permitidas de valores
     * @return boolean
     */
    protected function _checkValueRange() {
    	if($this->_value < $this->_minOrderValue || $this->_value > $this->_maxOrderValue) {
    		$this->_throwError('valueerror', 'Valor do pacote fora dos limites permitidos', __LINE__);
    		return false;
    	}
    	return true;
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

    /**
     * Verifica se o peso do pacote está dentro do mínimo e máximo permitidos
     * @return boolean
     */
    protected function _checkWeightRange() {
    	if (!$this->_packPartitioning) {
	    	if ($this->_weight > $this->_maxWeight) { // Checa se o peso excede o limite máximo
	    		$this->_throwError('maxweighterror', 'Limite de peso excedido', __LINE__);
	    		return false;
	    	}
    	}

    	if ($this->_weight <= 0) { // Checa se o peso do pacote é inferior a zero
    		$this->_throwError('weightzeroerror', 'Pacote com peso inferior ao permitido', __LINE__);
    		return false;
    	}

    	return true;
    }

    /**
     * Retorna mensagem de erro
     *
     * @param $message string
     * @param $log     string
     * @param $line    int
     * @param $custom  string
     */
    protected function _throwError($message, $log = null, $line = 'NO LINE', $custom = null){

    	$this->_result = null;
    	$this->_result = Mage::getModel('shipping/rate_result');

    	// Get error model
    	$error = Mage::getModel('shipping/rate_result_error');
    	$error->setCarrier($this->_code);
    	$error->setCarrierTitle($this->getConfigData('title'));

    	if($this->getConfigData($message)) {
        	if(is_null($custom)){
        		//Log error
        		Mage::helper('av5_correiospro')->log($this->_code . ' [' . $line . ']: ' . $log);
        		$error->setErrorMessage($this->getConfigData($message));
        	}else{
        		//Log error
        		Mage::helper('av5_correiospro')->log($this->_code . ' [' . $line . ']: ' . $log);
        		$error->setErrorMessage(sprintf($this->getConfigData($message), $custom));
        	}
    	} else {
    	    $error->setErrorMessage($message);
    	}

    	// Apend error
    	$this->_result->append($error);
    }

    /**
     * Calcula o peso cúbico do pacote
     */
    protected function _getCubicWeight(){
    	$cubicWeight = 0;
    	$items = $this->_request->getAllItems();
    	$maxH = $this->getConfigData('cubic_validation/max_height');
    	$minH = $this->getConfigData('cubic_validation/min_height');
    	$maxW = $this->getConfigData('cubic_validation/max_width');
    	$minW = $this->getConfigData('cubic_validation/min_width');
    	$maxD = $this->getConfigData('cubic_validation/max_depth');
    	$minD = $this->getConfigData('cubic_validation/min_depth');
    	$sumMax = $this->getConfigData('cubic_validation/sum_max');
    	$coefficient = $this->getConfigData('cubic_validation/coefficient');
    	$validate = $this->getConfigData('validate_dimensions');
    	foreach($items as $item){
    		$product = $item->getProduct();
    		$width = (!$product->getVolumeComprimento()) ? $this->_defWidth : $product->getVolumeComprimento();
    		$height = (!$product->getVolumeAltura()) ? $this->_defHeight : $product->getVolumeAltura();
   			$depth = (!$product->getVolumeLargura()) ? $this->_defDepth : $product->getVolumeLargura();

   			if (!$this->_packPartitioning && $validate && ($height > $maxH || $height < $minH || $depth > $maxD || $depth < $minD || $width > $maxW || $width < $minW || ($height+$depth+$width) > $sumMax)) {
   				return false;
   			}

   			$cubicWeight += (($width * $depth * $height) / $coefficient) * $item->getQty(); // Calcula o peso cúbico do item atual
    	}

    	$this->_cubic = $this->_fixWeight($cubicWeight);

    	return true;
    }

    /**
     * Adiciona os valores de envio para o retorno
     *
     * @param $shipping_method string
     * @param $shippingPrice float
     * @param $delivery_from integer
     * @param $delivery_to integer
     * @param $type_delivery integer
     * @param $correiosReturn array
     */
    protected function _appendShippingReturn($shipping_method, $shippingPrice = 0, $delivery_from = 0, $delivery_to = 0, $type_delivery = 2){
    	$method = Mage::getModel('shipping/rate_result_method');
    	$method->setCarrier($this->_code);
    	$method->setCarrierTitle($this->_title);
    	$method->setMethod($shipping_method);

    	$shippingCost = $shippingPrice;
    	$shippingPrice = $shippingPrice + $this->_handlingFee;

    	$methodTitle = $this->getConfigData('serv_' . $shipping_method . '/name');
    	$methodTerm = $this->getConfigData('serv_' . $shipping_method . '/term');

    	$custom_name = $this->getConfigData('custom_name_' . $shipping_method);
    	if ($custom_name != '') {
    		$methodTitle = $custom_name;
    	}

    	if($shipping_method == $this->getConfigData('acobrar_code')){
    		$methodTitle .= ' ( R$' . number_format($shippingPrice, 2, ',', '.') . ' )';
    		$shippingPrice = 0;
    	}

    	if ($shippingPrice == 0) {
    		$methodTitle .= " (Frete Grátis)";
    	}

    	if ($this->_showDelivery){
    		$delivery_from = (($delivery_from > 0) ? $delivery_from : $methodTerm) + $this->_addDeliveryDays;
    		$delivery_to = $delivery_to + $delivery_from; #Soma os dias extras geral ao prazo máximo informado;
    		if($type_delivery == 2){ # Prazo mínimo/máximo
    			$message = (sprintf($this->getConfigData('msgprazo_minmax'), $methodTitle, (int)($delivery_from), (int)($delivery_to)));
    		} else { # Prazo único
    			$message = (sprintf($this->getConfigData('msgprazo'), $methodTitle, (int)($delivery_to)));
    		}
    		$methodTitle = $message;
    	}
    	$method->setMethodTitle($methodTitle);

    	$method->setPrice($shippingPrice);
    	$method->setCost($shippingCost);

   		$this->_result->append($method);
    }

   	public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
		// Inicializa operações
   		if($this->_init($request) === false) return false;

   		// Verifica o país de destino
   		if (!$this->_checkCountry()) return $this->_result;

   		// Verifica os CEPs de destino e origem
   		if (!$this->_checkZipCode()) return $this->_result;

		// Verifica a faixa de valor do pedido
        if(!$this->_checkValueRange()) return $this->_result;

        // Verifica a faixa de peso do pacote
		if(!$this->_checkWeightRange()) return $this->_result;

        // Calcula o peso cúbico
        if($this->_getCubicWeight() === false){
        	$this->_throwError('dimensionerror', 'Dimension error', __LINE__);
            return $this->_result;
        }

        // Recupera as cotações de frete
        $this->_getQuotes();

        // Use descont codes
        $this->_updateFreeMethodQuote($request);

        return $this->_result;
   	}

   	/**
   	 * Recupera cotações de frete
   	 *
   	 * @return object
   	 */
   	protected function _getQuotes(){
   		$totalWeight = $this->_request->getPackageWeight();
   		$this->_request->setPostingMethods($this->_getAvailableMethods());
   		$this->_request->setPacCodes($this->_pacCodes);
   		$this->_request->setFixedPackageWeight($this->_fixWeight($totalWeight));
   		$this->_request->setCubicPackageWeight($this->_cubic);

   		$promos = Mage::getModel('av5_correiospro/promos');
   		$postcode = Mage::helper('av5_correiospro')->_formatZip($this->_request->getDestPostcode());

   		if ($this->_operation == 'ws') { // apenas webservice - online
   			$quotes = Mage::getModel('av5_correiospro/webservice');
   		} elseif ($this->_operation == 'off') { // apenas tabela offline
   			$quotes = Mage::getModel('av5_correiospro/price');
   		} else { // automático
   			$quotes = Mage::getModel('av5_correiospro/webservice');
   			$rates = $quotes->getRates($this->_request);
   			if (!$rates) { // Webservice Offline
   				# Inicializa Offline e recupera cotações
   				$quotes = Mage::getModel('av5_correiospro/price');
   			}
   		}

   		$rates = $this->_processRates($quotes);

   		$this->_request->setPackageWeight($totalWeight);
   		$this->_request->setFixedPackageWeight($this->_fixWeight($totalWeight));

   		$hide_rules = array();
   		$rates_to_show = array();

   		$dangerMessage = "";
   		foreach ($rates as $rate) {
   			$method = $rate['servico'];
   			if (strlen($method) == 4) {
   				$method = (string)'0' . $method;
   			}
   			$promo = $promos->getValid($method);

   			if ($rate['servico'] == '40045' && $this->_value > 10000) {
   				continue;
   			}

   			$danger = "";
   			if ($rate['areas_risco']) {
   				$danger_areas = @unserialize($rate['areas_risco']);
   				if (is_array($danger_areas)) {
	   				if (isset($danger_areas[$postcode])) {
	   					$danger = $danger_areas[$postcode];
	   				}
   				} else {
   					$danger = $rate['areas_risco'];
   				}
   				$dangerMessage = $danger;
   			}

   			if ($promo) {
   				if( $promo['desativar_servico'] == 1) { // é desativar serviço
   				    continue;
   				}

   				if ($promo['gratis']) {
   					$valor = 0.00;
   				} elseif( $promo['tipo_desconto'] == 1) { // é desconto fixo
   					$valor = $rate['valor'] - $promo['valor'];
   				} elseif( $promo['tipo_desconto'] == 2) { // é desconto percentual
   					$valor = $rate['valor'] * (1 - ($promo['valor']/100));
   				} elseif( $promo['tipo_desconto'] == 5) { // é desconto percentual do pedido
   					$valor = $rate['valor'] - ($this->_value * ($promo['valor']/100));
   				} elseif( $promo['tipo_desconto'] == 3) { // é acréscimo fixo
   					$valor = $rate['valor'] + $promo['valor'];
   				} elseif( $promo['tipo_desconto'] == 4) { // é acréscimo percentual
   					$valor = $rate['valor'] * (1 + ($promo['valor']/100));
   				} elseif( $promo['tipo_desconto'] == 6) { // é acréscimo percentual
   					$valor = $rate['valor'] + ($this->_value * ($promo['valor']/100));
   				} elseif( $promo['tipo_desconto'] == 7) { // é valor fixo do serviço
   			        $valor = $promo['valor'];
   				} else {
   					$valor = $rate['valor'];
   				}

   				if ($valor < 0) {
   					$valor = 0.00;
   				}

   				if ($promo['esconde_se']) {
                    $hide_rules[$promo['esconde_se']] = $method;
   				}

   				$rates_to_show[$method] = array(
   				    'valor'      => $valor,
   				    'prazo'      => $rate['prazo'],
   				    'danger'     => $danger,
   				    'promo_prazo'=> $promo['prazo'],
   				    'tipo_prazo' => $promo['tipo_prazo']
   				);
   			} else {
   			    $rates_to_show[$method] = array(
   			        'valor'      => $rate['valor'],
   			        'prazo'      => $rate['prazo'],
   			        'danger'     => $danger,
   			        'promo_prazo'=> 0,
   			        'tipo_prazo' => 2
   			    );
   			}
   		}

   		if (!empty($hide_rules)) {
   		    foreach($hide_rules as $tohide => $search) {
   		        if ($rates_to_show[$search] && $rates_to_show[$tohide]['valor'] > 0.00) {
   		            unset($rates_to_show[$tohide]);
   		        }
   		    }
   		}

   		if ($dangerMessage) {
   		    $this->_throwError($dangerMessage);
   		}
   		foreach ($rates_to_show as $service=>$values) {
   		    $this->_appendShippingReturn($service,$values['valor'],$values['prazo'],$values['promo_prazo'],$values['tipo_prazo']);
   		}

   		return $this->_result;
   	}

   	/**
   	 * Processa as cotações de frete particionando em pacotes
   	 *
   	 * @param $quotes
   	 * @return array
   	 */
   	protected function _processRates($quotes) {
   		$packages = array();
   		$waux = $this->_request->getPackageWeight();
   		if ($this->_packPartitioning) { // Particionamento habilitado
	   		$maxWeight = $this->_minMaxWeight();
	   		$count = 1;
	   		while($waux > $maxWeight) {
	   			$packages[] = $maxWeight;
	   			$waux = $waux - $maxWeight;
	   			$count++;
	   			if ($count == 10) break;
	   		}
   		}
   		$packages[] = $waux;

   		$returnData = array();
   		foreach($packages as $pack) {
   			$this->_request->setPackageWeight($pack);
   			$this->_request->setFixedPackageWeight($this->_fixWeight($pack));
   			$rates = $quotes->getRates($this->_request);
   			if (!$returnData) {
   				$returnData = $rates;
   			} else {
   				foreach ($rates as $rate) {
   					foreach ($returnData as $idx => $data) {
   						if ($data['servico'] == $rate['servico']) {
   							$returnData[$idx]['valor'] += $rate['valor'];
   						}
   					}
   				}
   			}
   		}

   		return $returnData;
   	}

   	/**
   	 * Encontra o menor Peso Máximo dos serviços selecionados
   	 *
   	 * @return integer
   	 */
   	protected function _minMaxWeight() {
   		$postingMethods = $this->_postingMethods;

   		$maxWeight = $this->_maxWeight;
   		if (is_array($postingMethods)) {
   			foreach ($postingMethods as $method) {
   				$weight = $this->getConfigData('serv_' . $method . '/maxweight');
   				if ($maxWeight > $weight) {
   					$maxWeight = $weight;
   				}
   			}
   		} else {
   			$maxWeight = $this->getConfigData('serv_' . $postingMethods . '/maxweight');
   		}

   		return $maxWeight;
   	}

   	/**
   	 * Informa se que o módulo aceita rastreamento
   	 *
   	 * @return boolean true
   	 */
   	public function isTrackingAvailable() {
   		return true;
   	}

   	/**
   	 * Retorna as informações do rastreador
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
   	 * Retorna o rastreamento
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
   	 * Recupera os dados de rastreamento direto dos Correios
   	 *
   	 * @param string $code
   	 * @return boolean
   	 */
   	protected function _getTracking($code) {
			$trackTable = 'main_table';
      $orderTable = Mage::getModel('sales/order')->getCollection()->getResource()->getTable('sales/order');

      $collection = Mage::getModel('sales/order_shipment_track')->getCollection();
      $collection->getSelect()->join($orderTable, "{$trackTable}.order_id = {$orderTable}.entity_id", array());
      $collection
        ->addFieldToFilter("{$trackTable}.carrier_code", 'av5_correiospro')
        ->addFieldToFilter("{$orderTable}.state", Mage_Sales_Model_Order::STATE_COMPLETE)
        ->addFieldToFilter("{$orderTable}.status", array('neq' => 'complete_delivered'))
        ->addFieldToFilter("{$trackTable}.track_number", $code);
      $collection->load();
			#Mage::log(Zend_Debug::dump($collection, null, false), 0, 'onestic_comprejunto.log');

			$progress = array();
			foreach ($collection as $t){
          $savedEvents = json_decode($t->getDescription(), true);
          $eventos = $savedEvents['evento'];

					foreach ($eventos as $evento){
						$locale = new Zend_Locale('pt_BR');
		   			$date = new Zend_Date($evento['data'], 'dd/MM/YYYY', $locale);

		   			$location = $evento['local'];
		   			if ($location) $location .= " - ";
		   			$location .= $evento['cidade'];
		   			if ($location) $location .= "/";
		   			$location .= $evento['uf'];

		   			$track = array(
		   				'deliverydate' => $date->toString('YYYY-MM-dd'),
		   				'deliverytime' => $evento['hora'],
		   				'deliverylocation' => htmlentities($location),
		   				'status' => htmlentities($evento['descricao']),
		   				'activity' => htmlentities($evento['descricao'])
		   			);

		   			if (isset($evento['detalhe'])) {
		   				$track['activity'] .= ' - ' . htmlentities($evento['detalhe']);
		   			}

		   			$progress[] = $track;
					}
			}

   		if (!empty($progress)) {
   			$track = end($progress);
   			$track['progressdetail'] = $progress;

   			$tracking = Mage::getModel('shipping/tracking_result_status');
   			$tracking->setTracking($code);
   			$tracking->setCarrier('correios');
   			$tracking->setCarrierTitle($this->getConfigData('title'));
   			$tracking->addData($track);

   			$this->_result->append($tracking);
   			return true;
   		} else {
   			$this->_result->append($error);
   			return false;
   		}
   	}

   	/**
   	 * Define o CEP como obrigatório
   	 *
   	 * @return boolean
   	 */
   	public function isZipCodeRequired($countryId = null)
   	{
   		return true;
   	}

   	/**
   	 * Retorna a lista de serviços permitidos
   	 *
   	 * @return array
   	 */
   	public function getAllowedMethods()
   	{
   		return array($this->_code => $this->getConfigData('title'));
   	}

   	/**
   	 * Gera frete grátis para um produto
   	 *
   	 * @param string $freeMethod
   	 * @return void
   	 */
   	protected function _setFreeMethodRequest($freeMethod)
   	{
   		// Set request as free method request
   		$this->_freeMethodRequest = true;
   		$this->_freeMethodRequestResult = Mage::getModel('shipping/rate_result');

   		$this->_postMethods = $freeMethod;
   		$this->_postMethodsExplode = array($freeMethod);

   		// Tranform free shipping weight
   		$this->_freeWeight = $this->_fixWeight($this->_freeWeight);

   		$this->_weight = $this->_freeWeight;
   		$this->_cubic = $this->_freeWeight;
   	}

   	/**
   	 * Verifica os métodos de entrega permitidos nos produtos do carrinho
   	 * Caso o produto não tenha nenhum método selecionado, então serão habilitados
   	 * todos os métodos selecionados na configuração do módulo
   	 */
   	protected function _getAvailableMethods(){
   		$items = Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();
   		$_availables = $this->_postingMethods;
   		foreach($items as $item){
   			$av_csv = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item->getProduct()->getId(), 'servicos_correios', Mage::app()->getStore()->getId());
			if ($av_csv) {
				$av_csv = explode(',',$av_csv);
				if (!is_array($av_csv)) {
					$av_csv = array($av_csv);
				}
   				$_availables = array_intersect($_availables, $av_csv);
			}
   		}

   		if ($_availables) {
   			return $_availables;
   		}

   		return $this->_postingMethods;
   	}
}
