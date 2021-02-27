<?php

class MageMigrator_Migcategory_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::export()
	 */
	public function export(){
		// exporta as categorias
		$this->exportCategories();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::import()
	 */
	public function import(){	
		// inicia o processo de importação
		$this->importCategories();	
	}
	
	/**
	 * @desc Retorna string com o caminho export catalog
	 * @return string
	 */
	public function getPath(){
		return Mage::getRoot() . '/../migrator/export/catalog/';
	}
	
	/**
	 * @desc Efetua o processo de exportar os objetos que envolvem as categorias do sistema, salvando em um arquivo txt
	 */
	private function exportCategories(){
		
		$path = Mage::getBaseUrl() . 'migrator/api/catalog_category.php';
		$pLog = Mage::getRoot() . '/../migrator/log/log.txt';
		foreach(Mage::app()->getStores() as $storeId => $store){
			$data = 'method=export&store='.$store->getWebsiteId();
			shell_exec("curl --data \"{$data}\" {$path} > {$pLog} &");
		}
		
	}
	
	/**
	 * @desc Dispara processo de importação das categorias e inicia processo de importação dos produtos
	 * @return void
	 */
	private function importCategories(){
			
		$path = Mage::getBaseUrl() . 'migrator/api/catalog_category.php';
		$pLog = Mage::getRoot() . '/../migrator/log/log.txt';	
		foreach(Mage::app()->getStores() as $storeId => $store){
			$data = 'method=import&store='.$store->getWebsiteId();
			shell_exec("curl --data \"{$data}\" {$path} > {$pLog} &");
			//$file = file($path . '?method=import&store=' . $store->getWebsiteId());
			//sleep(10);
		} 

	}
	
}