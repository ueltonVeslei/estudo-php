<?php

class MageMigrator_Migcatalogsearch_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {
		
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * @desc Retorna string com o caminho onde vão ficar os arquivos serializados
	 * @return string
	 */
	public function getPath(){
		return Mage::getRoot() . '/../migrator/export/catalogsearch/';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::export()
	 */
	public function export(){
		
		$process = Mage::getModel('migrator/process');
		$process->export('migcatalogsearch', null, 'exportFulltext');
		$process->export('migcatalogsearch', null, 'exportQuery');
		$process->export('migcatalogsearch', null, 'exportResult');
		$process->getManager()->work();
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::import()
	 */
	public function import(){
		
		$this->importFulltext();
		
	}
	
	/**
	 * @desc Inicia o processo exportação da tabela catalogsearch_fulltext
	 */
	public function exportFulltext(){
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		
		$fetFulltext = $read->fetchAll("SELECT * FROM catalogsearch_fulltext");
		
		$process = Mage::getModel('migrator/process');
		$process->export('migcatalogsearch', $fetFulltext, 'executeExportFulltext');
		$process->getManager()->work();
		
	}
	
	/**
	 * @desc Finaliza a exportação do fulltext salvando os collections em um arquivo serializado e criptografado
	 * @param array $collection
	 */
	public function executeExportFulltext($collection){
		
		/* cria valor randomico */
		$listChars = array(1,2,3,4,5,6,7,8,9);
		$rand = implode('', array_rand($listChars,2));
		
		$filename = microtime(true) . $rand . '.txt';
		
		file_put_contents($this->getPath() . 'fulltext/' . $filename, base64_encode(serialize($collection)));
		
	}
	
	/**
	 * @desc Inicia o processo exportação da tabela catalogsearch_query
	 */
	public function exportQuery(){
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		
		$fetQuery = $read->fetchAll("SELECT * FROM catalogsearch_query");
		
		$process = Mage::getModel('migrator/process');
		$process->export('migcatalogsearch', $fetQuery, 'executeExportQuery');
		$process->getManager()->work();
		
	}
	
	/**
	 * @desc Finaliza a exportação da Query salvando os collections em um arquivo serializado e criptografado
	 * @param array $collection
	 */
	public function executeExportQuery($collection){
		
		/* cria valor randomico */
		$listChars = array(1,2,3,4,5,6,7,8,9);
		$rand = implode('', array_rand($listChars,2));
		
		$filename = microtime(true) . $rand . '.txt';
		
		file_put_contents($this->getPath() . 'query/' . $filename, base64_encode(serialize($collection)));
		
	}
	
	/**
	 * @desc Inicia o processo exportação da tabela catalogsearch_result
	 */
	public function exportResult(){
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		
		$fetResult = $read->fetchAll("SELECT * FROM catalogsearch_result");
		
		$process = Mage::getModel('migrator/process');
		$process->export('migcatalogsearch', $fetResult, 'executeExportResult');
		$process->getManager()->work();
		
	}
	
	/**
	 * @desc Finaliza a exportação da Result salvando os collections em um arquivo serializado e criptografado
	 * @param array $collection
	 */
	public function executeExportResult($collection){
		
		/* cria valor randomico */
		$listChars = array(1,2,3,4,5,6,7,8,9);
		$rand = implode('', array_rand($listChars,2));
		
		$filename = microtime(true) . $rand . '.txt';
		
		file_put_contents($this->getPath() . 'result/' . $filename, base64_encode(serialize($collection)));
		
	}

	public function importFulltext(){
		
		if($this->getNextFulltextFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migcatalogsearch', $this->getNextFulltextFile(), 'executeImportFulltext');
			$process->getManager()->work();
		}else{
			$this->importQuery();
		}
		
	}

	public function importQuery(){
		
		if($this->getNextQueryFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migcatalogsearch', $this->getNextQueryFile(), 'executeImportQuery');
			$process->getManager()->work();
		}else{
			$this->importResult();
		}
		
	}

	public function importResult(){
		
		if($this->getNextQueryFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migcatalogsearch', $this->getNextResultFile(), 'executeImportResult');
			$process->getManager()->work();
		}
		
	}
	
	public function executeImportFulltext($filename){

		$localXmlString = file_get_contents(Mage::getRoot() . '/etc/local.xml');
		
		$collection = unserialize(base64_decode(file_get_contents($this->getPath() . 'fulltext/' . $filename)));
		foreach($collection as $key => $item){
			
			// monta o insert do item
			$sql = " INSERT INTO catalogsearch_fulltext SET ";
			$separator = '';
			foreach($item as $column => $value){
				$value = addslashes($value);
				$sql .= $separator . "{$column} = '{$value}'";
				$separator = ", ";
			}
			
			$this->executeQuery($sql);
			
		}
		
		unlink($this->getPath() . 'fulltext/' . $filename);
		$this->importFulltext();
	}
	
	public function executeImportQuery($filename){
		
		$collection = unserialize(base64_decode(file_get_contents($this->getPath() . 'query/' . $filename)));
		foreach($collection as $item){
			
			// monta o insert do item
			$sql = " INSERT INTO catalogsearch_query SET ";
			$separator = '';
			foreach($item as $column => $value){
				$value = addslashes($value);
				$sql .= $separator . "{$column} = '{$value}'";
				$separator = ', ';
			}
			
			$this->executeQuery($sql);
			
		}
		
		unlink($this->getPath() . 'query/' . $filename);
		$this->importQuery();
	}
	
	public function executeImportResult($filename){
		
		$collection = unserialize(base64_decode(file_get_contents($this->getPath() . 'result/' . $filename)));
		foreach($collection as $item){
			
			// monta o insert do item
			$sql = " INSERT INTO catalogsearch_result SET ";
			$separator = '';
			foreach($item as $column => $value){
				$value = addslashes($value);
				$sql .= $separator . "{$column} = '{$value}'";
				$separator = ', ';
			}
			
			$this->executeQuery($sql);
			
		}
		
		unlink($this->getPath() . 'result/' . $filename);
		$this->importResult();
	}
	
	public function executeQuery($sql){
		
		$localXmlString = file_get_contents(Mage::getRoot() . '/etc/local.xml');
		
		// pega as configurações de acesso ao banco contidas no local.xml
		$localXmlObj = simplexml_load_string($localXmlString, null, LIBXML_NOCDATA);
		$dataConnection = $localXmlObj->global->resources->default_setup->connection;
		
		$host = $dataConnection->host;
		$username = $dataConnection->username;
		$password = $dataConnection->password;
		$dbname = $dataConnection->dbname;
		
		// executa o comando o sql via linux
		shell_exec("mysql -h {$host} -u {$username} -p{$password} -D {$dbname} --execute=\"SET NAMES 'utf8'; {$sql}\" ");
		
	}
	/**
	 * @desc Retorna um nome de arquivo da pasta do fulltext se o não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextFulltextFile(){
		$file = false;
		$dir = dir($this->getPath() . 'fulltext/');
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
	/**
	 * @desc Retorna um nome de arquivo da pasta do query se o não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextQueryFile(){
		$file = false;
		$dir = dir($this->getPath() . 'query/');
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
	/**
	 * @desc Retorna um nome de arquivo da pasta do result se o não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextResultFile(){
		$file = false;
		$dir = dir($this->getPath() . 'result/');
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
}