<?php
class Av5_Correiospro_Model_Tracking {

    private $_ws			= NULL; // Endereço de acesso ao webservice
    private $_timeout		= NULL; // Tempo de espera de conexão ao servidor
    private $_user			= NULL; // Login de acesso ao webservice
    private $_pass			= NULL; // Senha de acesso ao webservice
    private $_type			= NULL; // Tipo de exibição dos objetos: L (lista) ou F (intervalo)
    private $_result		= NULL; // Formato do retorno da consulta de objeto: T (todos os eventos) ou U (apenas o último evento)
    private $_language		= NULL; // Idioma do retorno da consulta: 101 (Português) ou 102 (Inglês)
    private $_events		= NULL; // Eventos do objeto consultado
    private $_cronTracker	= FALSE; // Flag que indica o tipo de request

    const CARRIER_CODE 				= 'av5_correiospro';
    const ORDER_SHIPPED_STATUS 		= 'complete_shipped';
    const ORDER_DELIVERED_STATUS 	= 'complete_delivered';
    const ORDER_WARNED_STATUS 		= 'complete_warned';

    private function _init() {
        $helper = Mage::helper('av5_correiospro');
        $this->_ws = $helper->getConfigData('webservices/tracking/url');
        $this->_timeout = $helper->getConfigData('tracking/timeout');
        $this->_user = $helper->getSroUser();
        $this->_pass = $helper->getSroPass();
        $this->_type = $helper->getConfigData('webservices/tracking/type');
        $this->_events = NULL;
        if ($this->_cronTracker) {
        	$this->_result = $helper->getConfigData('tracking/result');
        } else {
        	$this->_result = $helper->getConfigData('webservices/tracking/result');
        }
        $this->_language = $helper->getConfigData('webservices/tracking/language');
    }

    public function events($code) {
        $this->_init();
        $object = array(
            'usuario'   => $this->_user,
            'senha'     => $this->_pass,
            'tipo'      => $this->_type,
            'resultado' => $this->_result,
            'lingua'    => $this->_language,
            'objetos'   => trim($code)
        );

        try {
        	$client = new SoapClient($this->_ws, array('connection_timeout' => $this->_timeout, 'cache_wsdl' => WSDL_CACHE_DISK));
        	$response = $client->buscaEventos($object);
        	if (empty($response)) {
        		throw new Exception("Empty response");
        	}
        	$this->_events = $response->return->objeto;
        } catch (Exception $e) {
        	Mage::log("Soap Error: {$e->getMessage()}");
        	return false;
        }

        return $this->_events;
    }

    public function getShippedTracks() {
    	$trackTable = 'main_table';
    	$orderTable = Mage::getModel('sales/order')->getCollection()->getResource()->getTable('sales/order');

    	$collection = Mage::getModel('sales/order_shipment_track')->getCollection();
    	$collection->getSelect()->join($orderTable, "{$trackTable}.order_id = {$orderTable}.entity_id", array());
    	$collection
	    	->addFieldToFilter("{$trackTable}.carrier_code", self::CARRIER_CODE)
	    	->addFieldToFilter("{$orderTable}.state", Mage_Sales_Model_Order::STATE_COMPLETE)
	    	->addFieldToFilter("{$orderTable}.status", array('in' => array(self::ORDER_SHIPPED_STATUS,self::ORDER_WARNED_STATUS)))
    		->addFieldToFilter("{$trackTable}.updated_at", array('lt' => date('Y-m-d H:i')));
    	$collection->load();
    	Mage::helper('av5_correiospro')->log('SQL PEDIDOS: ' . $collection->getSelect()->assemble());
    	return $collection;
    }

    public function checkTracks() {
    	$this->_cronTracker = TRUE;
    	$collection = $this->getShippedTracks();
    	foreach ($collection as $track) {
	    	$this->events($track->getNumber());
	    	if ($this->_events) {
	    		$wsdlEvent = json_decode(json_encode($this->_events->evento), true);
	    		$savedEvents = $track->getDescription();

          if($savedEvents !== null && (is_string($savedEvents) && $savedEvents !== 'null') && $this->validateJson($savedEvents)) {
            $events = json_decode($savedEvents, true)["evento"];
            $lastEvent = end($events);

            if(($lastEvent['tipo'] != $wsdlEvent['tipo']) || ($lastEvent['data'] != $wsdlEvent['data']) || ($lastEvent['hora'] != $wsdlEvent['hora'])){
              $savedEvents = json_decode($savedEvents, true);
              $evento = $savedEvents['evento'];
              array_push($evento, $wsdlEvent);
              $savedEvents['evento'] = $evento;
              $savedEvents = json_encode($savedEvents);

              $track->setDescription($savedEvents)->save();
  	    			$status = $this->getStatus();
  	    			if ($track->getShipment()->getOrder()->getStatus() <> $status) {
  		    			$track->getShipment()->getOrder()
  			    			->setStatus($this->getStatus())
                  ->save();
  	    			}
  	    			$track->getShipment()
  		    			->addComment($this->getComment(), $this->isNotify(), true)
  		    			->sendUpdateEmail($this->isNotify(), $this->getEmailComment())
                ->save();
            }
          } else {
            $evts = json_decode(json_encode($this->_events), true);
            $evts['evento'] = array($evts['evento']);
            $track->setDescription(json_encode($evts))->save();
            $status = $this->getStatus();
            if ($track->getShipment()->getOrder()->getStatus() <> $status) {
              $track->getShipment()->getOrder()
                ->setStatus($this->getStatus())
                ->save();
            }
            $track->getShipment()
              ->addComment($this->getComment(), $this->isNotify(), true)
              ->sendUpdateEmail($this->isNotify(), $this->getEmailComment())
              ->save();
          }
	    	}
    	}
    }

    public function validateJson($string) {
     $string = json_decode($string, true);
     return (json_last_error() == JSON_ERROR_NONE && array_key_exists("numero", $string));
    }

    public function getComment() {
    	$code = $this->_events->numero;
    	$evento = $this->_events->evento;
    	$msg = array();
    	$msg[] = $code;
    	$msg[] = "{$evento->cidade}/{$evento->uf}";
    	$msg[] = $evento->descricao;
    	if (isset($evento->destino) && isset($evento->destino->local)) {
    		$last = count($msg) - 1;
    		$msg[$last].= " para {$evento->destino->cidade}/{$evento->destino->uf}";
    	}
    	if (isset($evento->recebedor) && !empty($evento->recebedor)) {
    		$msg[] = Mage::helper('av5_correiospro')->__('Recebedor: %s', $evento->recebedor);
    	}
    	if (isset($evento->comentario) && !empty($evento->comentario)) {
    		$msg[] = Mage::helper('av5_correiospro')->__('Comentário: %s', $evento->comentario);
    	}
    	$msg[] = Mage::helper('av5_correiospro')->__('Evento: %s', "{$evento->tipo}/{$evento->status}");
    	return implode(' | ', $msg);
    }

    public function getEmailComment() {
    	$trackUrl = Mage::helper('av5_correiospro')->getConfigData('tracking/url');
    	$code = $this->_events->numero;
    	$evento = $this->_events->evento;
    	$htmlAnchor = $code . ' &nbsp;<form action="'.$trackUrl.'" method="POST"><input type="hidden" name="Objetos" value="'.$code.'" /><input type="submit" value="Consultar" /></form>';
    	$msg = array();
    	$msg[] = Mage::helper('av5_correiospro')->__('Rastreador: %s', $htmlAnchor);
    	$msg[] = Mage::helper('av5_correiospro')->__('Local: %s', "{$evento->cidade}/{$evento->uf}");
    	$msg[] = Mage::helper('av5_correiospro')->__('Situação: %s', $evento->descricao);
    	if (isset($evento->recebedor) && !empty($evento->recebedor)) {
    		$msg[] = Mage::helper('av5_correiospro')->__('Recebedor(a): %s', $evento->recebedor);
    	}
    	if (isset($evento->comentario) && !empty($evento->comentario)) {
    		$msg[] = Mage::helper('av5_correiospro')->__('Comentário: %s', $evento->comentario);
    	}
    	if (isset($evento->destino)) {
    		$destino = $evento->destino;
    		$msg[] = Mage::helper('av5_correiospro')->__('Destino: %s', "{$destino->cidade}/{$destino->uf}");
    	}
    	return implode('<br />', $msg);
    }

    public function validate($mode) {
    	$helper = Mage::helper('av5_correiospro');
    	$isValid = false;
    	$evento = $this->_events->evento;
    	$hashTypes = explode(',', $helper->getConfigData("tracking/{$mode}/type"));
    	if (in_array($evento->tipo, $hashTypes)) {
    		$type = strtolower($evento->tipo);
    		$hashStatus = explode(',', $helper->getConfigData("tracking/{$mode}/{$type}"));
    		$isValid = in_array((int) $evento->status, $hashStatus);
    	}
		return $isValid;
    }

    public function isNotify() {
    	return $this->validate('notify');
    }

    public function getStatus() {
    	$status = self::ORDER_SHIPPED_STATUS;
    	if ($this->validate('warn')) {
    		$status = self::ORDER_WARNED_STATUS;
    	}
    	if ($this->validate('last')) {
    		$status = self::ORDER_DELIVERED_STATUS;
    	}
    	return $status;
    }

}
