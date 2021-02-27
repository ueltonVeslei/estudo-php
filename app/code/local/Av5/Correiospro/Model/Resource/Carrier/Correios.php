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
 * Av5_Correiospro_Model_Mysql4_Carrier_Correios
 *
 * @category   Shipping
 * @package    Av5_Correiospro
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */
class Av5_Correiospro_Model_Resource_Carrier_Correios extends Mage_Core_Model_Resource_Db_Abstract {
    
	/**
	 * Construtor da classe
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 */
	protected function _construct(){
        $this->_init('av5_correiospro/correios', 'id');
    }
	
    /**
     * Recupera os pre�os de frete baseado no request do usu�rio
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return multitype:unknown
     */
	public function getRates(Mage_Shipping_Model_Rate_Request $request) {
        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

		$postcode = Mage::helper('av5_correiospro')->_formatZip($request->getDestPostcode());
        $table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
		
        $pkgWeight = ceil($request->getFixedPackageWeight());
        $postingMethods = $request->getPostingMethods();
        if ($request->getFixedPackageWeight() < 0.5) {
        	$pacCodes = $request->getPacCodes();
        	$hasPac = array_intersect($postingMethods, $pacCodes);
        	$pacString = "";
        	if ($hasPac) {
        		$pacString = "(servico in (" . implode(',',$hasPac) . ") AND peso = '" . $pkgWeight . "') OR ";
        		$postingMethods = array_diff($postingMethods, $hasPac);
        	}
        	
        	if ($request->getFixedPackageWeight() < 0.3) {
        		$weightCond = '0.300';
        	} else {
        		$weightCond = '0.500';
        	}
        	
        	$servicesString = "(" . $pacString . "(servico in (" . implode(",", $postingMethods) . ") AND peso = '".$weightCond."') )";
        } else {
        	$servicesString = "(servico in (" . implode(",", $postingMethods) . ") AND peso = '" . $pkgWeight . "')";
        }
        
        $searchString = " AND (cep_destino_ini <= '" . $postcode . "' AND cep_destino_fim >= '" . $postcode . "') AND store_id = " . $request->getStoreId();
		
		
		$select = $read->select()->from($table);
		$select->where(
				$servicesString.
				$searchString
			);
		
		$newdata=array();
		$row = $read->fetchAll($select);
		if (!empty($row))
		{
			foreach ($row as $data) {
				$newdata[]=$data;
			}
		}
		return $newdata;
    }
    
    /**
     * Lista os registros desatualizados com base nos servi�os, frequencia e limite
     * @param array $postMethods
     * @param int $frequency
     * @param int $limit
     * @return array
     */
    public function listServices($postMethods, $frequency, $limit) {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
		
    	$select = $read->select()->from($table);
    	$select->where("(lastupdate IS NULL OR lastupdate < SUBDATE(NOW(),".$frequency.")) AND servico in (".$postMethods.")");
    	$select->limit($limit);
    	
    	return $read->fetchAll($select);
    }
    
    /**
     * Lista os serviços que precisam de atualização junto com a quantidade de registros
     * desatualizados
     * @param arra $postMethods
     * @param int $frequency
     * @return array
     */
    public function toUpdate($postMethods, $frequency) {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
    	
    	$select = $read->select()->from($table,array("servico","count(valor) as total"));
    	$select->where("(lastupdate IS NULL OR lastupdate < SUBDATE(NOW(),".$frequency.")) AND servico in (".$postMethods.")");
    	$select->group("servico");

    	return $read->fetchAll($select);
    }
    
    /**
     * Retorna a quantidade de registros desatualizados de um servi�o
     * @param string $postMethod
     * @param int $frequency
     * @return array
     */
    public function hasToUpdate($postMethod, $frequency) {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
    	 
    	$select = $read->select()->from($table,array("servico","count(valor) as total"));
    	$select->where("(lastupdate IS NULL OR lastupdate < SUBDATE(NOW(),".$frequency.")) AND servico = ".$postMethod);
    	$select->group("servico");
    
    	return $read->fetchRow($select);
    }
    
    /**
     * Retorna a quantidade de registros atualizados de um serviço
     * @param string $postMethod
     * @param int $frequency
     * @return array
     */
    public function updatedCount($postMethod, $frequency) {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
    
    	$select = $read->select()->from($table,array("servico","count(valor) as total"));
    	$select->where("lastupdate >= SUBDATE(NOW(),".$frequency.") AND servico = ".$postMethod);
    	$select->group("servico");
    	
    	return $read->fetchRow($select);
    }
    
    /**
     * Verifica se o serviço está presente no banco de dados
     * @param string $service
     * @return boolean
     */
    public function isPopulated($service) {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
    	 
    	$select = $read->select()->from($table,array("count(valor) as total"));
    	$select->where("servico = ".$service);
    	
    	$result = $read->fetchRow($select);
    	if (!$result['total']) {
    		return false;
    	}
    	
    	return true;
    }
    
    /**
     * Atualiza o serviço informado com os dados recebidos
     * @param string $code
     * @param string $zip
     * @param array $data
     */
    public function updateServiceBy($code, $zip, $weight, $data) {
        if ($this->isPopulated($code)) {
        	$write = $this->_getWriteAdapter();
        	$read = $this->_getReadAdapter();
        	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
        	$select = $read->select()->from($table,array("id","areas_risco"));
        	$select->where("(cep_destino_ini <= '" . $zip . "' AND cep_destino_fim >= '" . $zip . "') AND servico = '".$code."' AND peso = " . $weight);
        	$row = $read->fetchRow($select);
        	if ($row) {
	        	if (isset($data['areas_risco'])) {
	        		if ($data['areas_risco']){
	    	    		$areas_risco = array();
	    	    		if($row['areas_risco']) {
	    	    			$areas_risco = unserialize($row['areas_risco']);
	    	    		}
	    	    		$areas_risco[$zip] = $data['areas_risco'];
	        			$data['areas_risco'] = serialize($areas_risco);
	        		} else {
	        			$data['areas_risco'] = $row['areas_risco'];
	        		}
	        	}
	        	
	        	$rows = $write->update($table, $data, "id = " . $row['id']);
        	}
        }
    }
    
    /**
     * Atualiza o serviço informado com os dados recebidos
     * @param int $id
     * @param array $data
     */
    public function updateService($id, $data) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');

    	$rows = $write->update($table, $data, "id = " . $id);
    }
    
    /**
     * Exclui o serviço informado
     * @param int $id
     */
    public function deleteService($id) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
    
    	$rows = $write->delete($table, "id = " . $id);
    }
    
    /**
     * Limpa a tabela de preços
     */
    public function cleanDatabase() {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
    
    	$rows = $write->delete($table, "1");
    }
    
    /**
     * Popula o banco de dados com os dados padrão para os serviços informados
     * @param array $services
     * @param double $maxWeight
     * @param string $from
     */
    public function populate($services, $from) {
    	$read = $this->_getReadAdapter();
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correiospro/correios');
    	
    	# Recupera os IDs de todos os websites e lojas da instalação atual
    	$ws = array();
    	foreach (Mage::app()->getWebsites() as $website) {
    		foreach ($website->getGroups() as $group) {
    			$stores = $group->getStores();
    			foreach ($stores as $store) {
    				$ws[$store->getId()] = $website->getId();
    			}
    		}
    	}
    	
    	foreach ($services as $service) {
    		if (in_array($service[0],array(81019,40215,40290))) {
    			$defaultData = $this->_81019data;
    		} else {
    			$defaultData = $this->_defaultData;
    		}
    		foreach ($defaultData as $record) {
    			$select = $read->select()->from($table,array("max(peso) as total"));
    			$select->where("servico = ".$service[0]);
    			$select->where("regiao like '".$record[0]."'");
    			$result = $read->fetchRow($select);
    			
    			if (!$result['total']) {
    				$w = $service[4];
    			} else {
    				$w = ceil($result['total'])+1;
    				if(!$w) $w = $service[4];
    			}
    			
    			if ($w > $service[3])
    				continue;
    			
    			$insertData = array(
    					'servico' 			=> $service[0],
    					'nome'				=> $service[1],
    					'regiao'			=> $record[0],
    					'prazo'				=> $service[2],
    					'peso'				=> 0,
    					'valor'				=> '0.00',
    					'cep_origem'		=> Mage::getStoreConfig('shipping/origin/postcode', $store),
    					'cep_destino_ini'	=> $record[1],
    					'cep_destino_fim'	=> $record[2],
    					'lastupdate'		=> 'NULL',
    					'cep_destino_ref'	=> $record[3],
    					'store_id'			=> $store,
    					'website_id'		=> $website
    			);
    			
    			if ($w == $service[4] && $service[4] < 1) { // INCLUI REGISTRO DOS PESOS MENORES QUE 1KG
    				$insertData['peso'] = $service[4];
    				foreach($ws as $store=>$website) {
    					$write->insert($table, $insertData);
    				}
    				$insertData['peso'] = '0.500';
    				foreach($ws as $store=>$website) {
    					$write->insert($table, $insertData);
    				}
    				$w = 1;
    			}

    			for($weight = $w; $weight <= $service[3]; $weight++) {
	    			try {
	    				$insertData['peso'] = $weight;
	    				foreach($ws as $store=>$website) {
		    				$write->insert($table, $insertData);
	    				}
	    			} catch (Exception $e) {
	    				Mage::helper('av5_correiospro')->log($e->getMessage() . " > Serviço: " . $service[1] . "(" . $service[0] . ") - CEP:" . $record[1] . " a " . $record[2] . " - Peso: " . $weight);
	    			}
    			}
    		}
    	}
    }
}
