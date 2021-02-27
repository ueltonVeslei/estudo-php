<?php

class MageMigrator_Migrator_Model_Process_Gearman extends MageMigrator_Migrator_Model_Process_Abstract {
	
	private $gearmanInstance = null;
	private $model = null;
	private $collection = null;
	private $method = null;
	
	public function __construct(){
		if(class_exists('Gearman')){
			$this->gearmanInstance = new Gearman();
			$this->gearmanInstance->addServer();
		}
	}
	
	public function addExportProcess($model, $collection, $method){
		
		if($this->gearmanInstance === null){
			return;
		}
		
		$tituloProcess = microtime() . '_' . $method;
		$this->gearmanInstance->addFunction($tituloProcess, 'execute');
		
		function execute(){
			global $model, $collection, $method;
			$modelInstance = Mage::getModel($model . '/standard');
			$modelInstance->$method($collection);
		}
		
	}
	
	public function addImportProcess($model, $collection, $method){
		
		if($this->gearmanInstance === null){
			return;
		}
		
		$tituloProcess = microtime() . '_' . $method;
		$this->gearmanInstance->addFunction($tituloProcess, 'execute');
		
		function execute(){
			global $model, $collection, $method;
			$modelInstance = Mage::getModel($model . '/standard');
			$modelInstance->$method($collection);
		}
		
	}
	
	public function work(){
		while($this->gearmanInstance->work());
	}
	
	public function isActive(){
		if($this->gearmanInstance === null){
			return false;
		}
		return true;
	}
	
}