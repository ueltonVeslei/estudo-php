<?php

abstract class MageMigrator_Migrule_Helper_Abstract extends Mage_Core_Helper_Abstract {
	
	protected $_read = null;
	protected $_write = null;
	
	protected $_exportMethods = array(
		'_export15' => array('1.2','1.3','1.4','1.5'),	
		'_export17' => array('1.6','1.7')
	);
	
	protected $_importMethods = array(
		'_import15' => array('1.2','1.3','1.4','1.5'),	
		'_import17' => array('1.6','1.7')
	);
	
	/**
	 * @desc Factory Method - para a exportação das regras de carrinho baseada na versão informada
	 * @param $version - Numero da versão do magento. Ex: 1.4.0.1
	 * @return array|false
	 */
	public function execute($action, $version, $data = null){
		
		// seta as connexões
		$this->_read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->_write = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		// valida numero da versão
		if(!preg_match('/^[0-9]\.[0-9]\.[0-9]\.[0-9]$/',$version)){
			return false;
		}
		
		// pega a chave da versão
		$versionKey = substr($version,0,3);
		$versionMethod = false;
		
		$attribute = "_{$action}Methods";

		foreach($this->$attribute as $method => $versions){
			if(in_array($versionKey, $versions)){
				$versionMethod = $method;
			}
		}
		
		if($versionMethod){
			return $this->$versionMethod($data);
		}
		
		return false;
		
	}
	
	abstract protected function _import15($data);
	abstract protected function _export15();
	 
	abstract protected function _import17($data); 
	abstract protected function _export17(); 
	
}