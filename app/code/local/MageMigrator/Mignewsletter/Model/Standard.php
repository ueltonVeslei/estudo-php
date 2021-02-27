<?php

class MageMigrator_Mignewsletter_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {
	
	/**
	 * @desc Retorna string com o caminho para as imagens dos produtos na pasta media
	 * @return string
	 */
	public function getPath(){
		return Mage::getRoot() . '/../migrator/export/newsletter/';
	}
	
	public function export(){
		
		// exporta os clientes das news
		$this->exportSubscribes();
		
	}
	
	public function import(){
	
		// import os cliente das news
		$this->importSubscribes();
		
	}
	
	public function exportSubscribes(){
		
		$collection = Mage::getModel('newsletter/subscriber')->getCollection();
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$fetchAll = $read->fetchAll("SELECT subscriber_id FROM newsletter_subscriber");
		
		$listSubscribes = array();
		foreach($fetchAll as $subscribe){
			$listSubscribes[] = $subscribe['subscriber_id'];
		}
		
		$process = Mage::getModel('migrator/process');
		$process->export('mignewsletter', $listSubscribes, 'executeExportSubscribes');
		$process->getManager()->work();
		
	}
	
	public function executeExportSubscribes($collection){
		
		$listSubscribes = array();
		foreach($collection as $subscribe){			
			$listSubscribes[] = Mage::getModel('newsletter/subscriber')->load($subscribe)->getData();
		}
		
		/* cria valor randomico */
		$listChars = array(1,2,3,4,5,6,7,8,9);
		$rand = implode('', array_rand($listChars,2));
		
		$filename = microtime(true) . $rand . '.txt';
		
		file_put_contents($this->getPath() . 'subscribe/' . $filename, base64_encode(serialize($listSubscribes)));
		
	}
	
	public function importSubscribes(){
		if($this->getNextSubscribeFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('mignewsletter', $this->getNextSubscribeFile(), 'executeImportSubscribes');
			$process->getManager()->work();
		}
	}
	
	public function executeImportSubscribes($filename){
		
		$listSubscribes = unserialize(base64_decode(file_get_contents($this->getPath() . 'subscribe/' . $filename)));
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		foreach($listSubscribes as $subscribe){
			
			$load = Mage::getModel('newsletter/subscriber')->load($subscribe['subscriber_id']);
			if($load->getData('subscriber_email')){
				continue;
			}
			
			$count = false;
			$sql = 'INSERT INTO newsletter_subscriber  SET ';
			foreach($subscribe as $column => $value){
				$value = addslashes($value);
				$sql .= ($count)? ', ': '';
				$sql .= " {$column} = '{$value}'";
				$count = true;
			}
			
			$write->query($sql);
		}
		
		unlink($this->getPath() . 'subscribe/' . $filename);
		$this->importSubscribes();
		
	}
	
	/**
	 * @desc Retorna um nome de arquivo da pasta de cliente das newsletter se não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextSubscribeFile(){
		$file = false;
		$dir = dir($this->getPath() . 'subscribe/');
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
		
}