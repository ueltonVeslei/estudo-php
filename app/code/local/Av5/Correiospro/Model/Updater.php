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
 * Av5_Correiospro_Model_Updater
 *
 * @category   Shipping
 * @package    Av5_Correiospro
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */

class Av5_Correiospro_Model_Updater extends Varien_Object {
	
	/**
	 * Propriedades da classe 
	 */
	protected $_code				= "av5_correiospro";
	protected $_from				= NULL; // CEP de origem
	protected $_postingMethods		= NULL; // Serviços de postagem
	protected $_limitRecords		= NULL; // Número de registros atualizados por iteração
	protected $_initiated			= false; // Controla se as variáveis já foram inicializadas
	protected $_store				= NULL; // ID da loja atual
	protected $_allowedErrors		= array("009","010","011"); // Códigos de erro permitidos, controle de área de risco
	
	/**
	 * Inicializa as propriedades da classe
	 */
	protected function _init() {
		if (!$this->_initiated) {
			$this->_postingMethods = Mage::helper('av5_correiospro')->getConfigData('posting_methods');
            $this->_postingMethods = explode(',', $this->_postingMethods);

			$this->_updateFrequency = Mage::helper('av5_correiospro')->getConfigData('update_frequency');
			$this->_limitRecords = Mage::helper('av5_correiospro')->getConfigData('limit_records');
			$this->_from = Mage::helper('av5_correiospro')->_formatZip(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
			$this->_initiated = true;
		}
	}
	
	/**
	 * Encontra o maior peso dos serviços selecionados
	 */
	protected function _getGreaterWeight() {
		$greaterWeight = 0;
		foreach($this->_postingMethods as $method) {
			$maxWeight = Mage::helper('av5_correiospro')->getConfigData('serv_' . $method . '/maxweight');
			if ($maxWeight > $greaterWeight) {
				$greaterWeight = $maxWeight;
			}
		}
		
		return $greaterWeight;
	}
	
	/**
	 * Encontra o maior valor permitido dos serviços selecionados
	 */
	protected function _getGreaterValue() {
		$greaterValue = 0;
		foreach($this->_postingMethods as $method) {
			$maxValue = Mage::helper('av5_correiospro')->getConfigData('serv_' . $method . '/maxvalue');
			if ($maxValue > $greaterValue) {
				$greaterValue = $maxValue;
			}
		}
		
		return $greaterValue;
	}
	
	
	/**
	 * Retorna todos os serviços habilitados na loja
	 * @return array
	 */
	public function allServices(){
		$this->_init();
		return $this->_postingMethods;
	} 
	
	/**
	 * Executa atualização de tabela de preços
	 * @param string $services
	 */
	public function update($state=null) {
		$this->_init();
		
		$model = Mage::getModel('av5_correiospro/price');
		$webservice = Mage::getSingleton('av5_correiospro/webservice');
		
		$totalSuccess = $totalErrors = 0;
		$cep_origem = $this->_from;
		
		$request = Mage::getModel('shipping/rate_request');
		$request->setPostingMethods($this->_postingMethods);
		$request->setDestCountryId('BR');
		$request->setPackageValue(100);
		
		foreach($model->getUpdateCollection($state, $this->_updateFrequency, $this->_limitRecords) as $row) {
			$error = false;
			
			$startZip = $row->getStart();
			
			if ($startZip <> $row->getEnd()) {
				if (substr($startZip, -3) == '000') {
					$startZip = substr($startZip, 0, 7) . '1';
				}
			}
			
			$request->setDestPostcode($startZip);
						
			// Atualiza peso 0.3
			$request->setPackageWeight(0.3);
			$request->setFixedPackageWeight(0.3);
			$data = $webservice->getRates($request);
			
			if (!$data) {
				$error = true;
			}
			
			// Atualiza peso 0.5
			$request->setPackageWeight(0.5);
			$request->setFixedPackageWeight(0.5);
			$data = $webservice->getRates($request);
			
			if (!$data) {
				$error = true;
			}
			
			for($i = 1; $i <= $this->_getGreaterWeight(); $i++) {
				$request->setPackageWeight($i);
				$request->setFixedPackageWeight($i);
				$data = $webservice->getRates($request);
				
				if (!$data) {
					$error = true;
				}
			}
			
			if (Mage::helper('av5_correiospro')->getConfigData('declared_value')) {
				$request->setPackageValue(100);
				$data = $webservice->getRates($request);
					
				if (!$data) {
					$error = true;
				}
				
				$pkgValue = 500;
				while ($pkgValue <= $this->_getGreaterValue()) {
					$request->setPackageValue($pkgValue);
					$data = $webservice->getRates($request);
						
					if (!$data) {
						$error = true;
					}

					$pkgValue += 500;
				}
			}
			
			if ($error) {
				$totalErrors++;
			} else {
				$totalSuccess++;
			}
		}
		return array("success" => $totalSuccess, "errors" => $totalErrors);
	}
	
	/**
	 * Retorna a lista de serviços que precisam de atualização
	 * @return array
	 */
	public function toUpdate() {
		$this->_init();
		$model = Mage::getResourceModel('av5_correiospro/carrier_correios');
		return $model->toUpdate($this->_postingMethods, $this->_updateFrequency);
	}
	
	/**
	 * Retorna o numero de registros desatualizados de um serviço
	 * @param Zend_Db_Table_Row
	 */
	public function hasToUpdate($method){
		$this->_init();
		$model = Mage::getResourceModel('av5_correiospro/carrier_correios');
		return $model->hasToUpdate($method, $this->_updateFrequency);
	}
	
	/**
	 * Retorna o número de registros atualizados de um serviço
	 * @param Zend_Db_Table_Row
	 */
	public function updatedCount($method){
		$this->_init();
		$model = Mage::getResourceModel('av5_correiospro/carrier_correios');
		return $model->updatedCount($method, $this->_updateFrequency);
	}
	
	public function getServiceName($service) {
		return $this->getConfigData('serv_' . $service . '/name');
	}
	
	public function stillUpdate($service) {
		$model = Mage::getResourceModel('av5_correiospro/carrier_correios');
		$result = $model->hasToUpdate($service, $this->_updateFrequency);
		
		if ($result['total'] > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Popula a tabela de preços com os dados básicos para os serviços selecionados
	 */
	public function populate($last) {
		$this->_init();
		$page = ceil($last/100);
		
		if (Mage::getModel('av5_correiospro/location')->getCollection()->getSize() <= 0) {
			Mage::getModel('av5_correiospro/location')->populate();
		}
		
		$locations = Mage::getModel('av5_correiospro/location')->getCollection()->setPageSize(100)->setCurPage($page+1);
		$success = $errors = 0;
		$count = $last;
		foreach ($locations as $location) {
			$price = Mage::getModel('av5_correiospro/price')->load($location->getId(),'region');
			if (!$price->getId()) {
				$price = Mage::getModel('av5_correiospro/price');
				$price->setRegion($location->getId());
				$price->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
				$price->setStoreId(Mage::app()->getStore()->getStoreId());
				$price->setSource($this->_from);
				$price->save();
			}
			$count++;
		}
		
		return $count;
	}
	
	private function _getMethodData($method) {
		$name = Mage::helper('av5_correiospro')->getConfigData('serv_' . $method . '/name');
		$term = Mage::helper('av5_correiospro')->getConfigData('serv_' . $method . '/term');
		$weight = Mage::helper('av5_correiospro')->getConfigData('serv_' . $method . '/maxweight');
		$minWeight = Mage::helper('av5_correiospro')->getConfigData('serv_' . $method . '/minweight');
		return array(
				'code'		=> $method,
				'name'		=> $name,
				'term'		=> $term,
				'maxWeight'	=> $weight,
				'minWeight'	=> $minWeight
		);
	}
	
	/**
	 * Verifica atualizações nos rastreamentos
	 */
	public function tracking() {
		if (!Mage::helper('av5_correiospro')->getConfigData('tracking_monitor')) {
			return false;
		}
		Mage::getModel('av5_correiospro/tracking')->checkTracks();
	}
	
}