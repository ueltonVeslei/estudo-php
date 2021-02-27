<?php

abstract class MageMigrator_Migrator_Model_Process_Abstract extends Mage_Core_Model_Abstract {
	
	abstract public function addExportProcess($model, $collection, $method);
	abstract public function addImportProcess($model, $collection, $method);
	abstract public function work();
	abstract public function isActive();
	
	/**
	 * @desc Retorna o caminha para o arquivo de log do magemigrator
	 */
	protected function getPathLog(){
		$path = Mage::getRoot() . '/../migrator/log/log.txt';
		return $path;
	}

	public function getBaseUrl(){
		return "http://{$_SERVER['HTTP_HOST']}";
	}
	
} 