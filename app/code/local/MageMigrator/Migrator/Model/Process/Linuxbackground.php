<?php

class MageMigrator_Migrator_Model_Process_Linuxbackground extends MageMigrator_Migrator_Model_Process_Abstract {
	
	public function addExportProcess($model, $collection, $method){
		
		if(is_array($collection)){
			
			/* cria valor randomico */
			$listChars = array(1,2,3,4,5,6,7,8,9);
			$rand = implode('', array_rand($listChars,2));
			
			$filename = microtime(true) . $rand . '.txt';
			
			// Grava os dados em um arquivo
			file_put_contents(Mage::getRoot() . '/../migrator/export/collections/' . $filename, serialize($collection));
			
			// executa o processo em background
			$path = $this->getBaseUrl() . '/migrator/index/backgroundExport/model/' . $model . '/method/' . $method . '/collection/' . $filename . '/';
		}else{
			// executa o processo em background
			$path = $this->getBaseUrl() . '/migrator/index/backgroundExport/model/' . $model . '/method/' . $method . '/collection/' . $collection . '/';
		}
		
		$pLog = $this->getPathLog();
		
		shell_exec("curl -s {$path} > {$pLog} &");
		
	}
	
	public function addImportProcess($model, $collection, $method){
		
		// executa o processo em background
		$path = $this->getBaseUrl() . '/migrator/index/backgroundImport/model/' . $model . '/method/' . $method . '/collection/' . $collection . '/';
		
		$pLog = $this->getPathLog();
		
		shell_exec("curl -s {$path} > {$pLog} &");
		
	}
	
	public function work(){
		return;
	}
	
	public function isActive(){
		return true;
	}
	
}