<?php

class MageMigrator_Migrator_Model_Process extends Mage_Core_Model_Abstract {
	
	private $manager = null;
	
	/**
	 * @desc Adiciona um processo de exportação 
	 * @param string $model
	 * @param array $collection
	 * @param string $method
	 * @return this
	 */
	public function export($model, $collection = null, $method, $total_in_array = 250){
		
		if(is_array($collection)){
			$colChunk = array_chunk($collection, $total_in_array);
			foreach($colChunk as $newCollection){
				$this->getManager()->addExportProcess($model, $newCollection, $method);
			}
		}else{
			$this->getManager()->addExportProcess($model, $collection, $method);
		}
		
		return $this;
	}

	/**
	 * @desc Adiciona um processo de importação
	 * @param string $model
	 * @param string $collection
	 * @param string $method
	 * @return this
	 */
	public function import($model, $collection, $method){
		
		$this->getManager()->addImportProcess($model, $collection, $method);
		return $this;
		
	}
	
	/**
	 * @desc Retorna instancia do gerenciador de processos que está ativo
	 */
	public function getManager(){
		/*
		if($this->manager != null){
			return $this->manager;
		}
		
		$process = Mage::getModel('migrator/process_gearman');
		
		if($process->isActive()){
			$this->manager = $process;
			return $this->manager;
		}
		*/
		$this->manager = Mage::getModel('migrator/process_linuxbackground');
		return $this->manager;
	}
	
}