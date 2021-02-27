<?php

class MageMigrator_Migrule_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::export()
	 */
	public function export(){
		
		// exporta as regras de compra
		$this->exportSalesRule();
		
		// exporta as regras de carrinho
		//$this->exportCatalogRule();
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::import()
	 */
	public function import(){
		
		// importa as regras de compra
		$this->importSalesRule();
		
		// importa as regras de carrinho
		//$this->importCatalogRule();
		
	}
	
	/**
	 * @desc Retorna string com o caminho para as imagens dos produtos na pasta media
	 * @return string
	 */
	public function getPath(){
		return Mage::getRoot() . '/../migrator/export/rules/';
	}
	
	/**
	 * @desc Executa a exportação das regras de compras
	 * @return void
	 */
	public function exportSalesRule(){

		$helper = Mage::helper('migrule/sales');
		$listSalesRule = $helper->execute('export',Mage::getVersion());
		
		$process = Mage::getModel('migrator/process');
		$process->export('migrule', $listSalesRule, 'executeExportSalesRule', 300);
		$process->getManager()->work();
		
	}
	
	public function executeExportSalesRule($collection){
		
		/* cria valor randomico */
		$listChars = array(1,2,3,4,5,6,7,8,9);
		$rand = implode('', array_rand($listChars,2));
		
		$filename = microtime(true) . $rand . '.txt';
		
		// salva os dados num arquivo serializado
		file_put_contents($this->getPath() . 'salesrule/' . $filename, base64_encode(serialize($collection)));
		
	}
	
	public function executeExportSalesRuleCustomers($collection){
		
		/* cria valor randomico */
		$listChars = array(1,2,3,4,5,6,7,8,9);
		$rand = implode('', array_rand($listChars,2));
		
		$filename = microtime(true) . $rand . '.txt';
		
		// salva os dados num arquivo serializado
		file_put_contents($this->getPath() . 'salesrulecustomers/' . $filename, base64_encode(serialize($collection)));
		
	}
	
	/**
	 * @desc Dispara processo de importação das promoções de carrinho
	 * @return void
	 */
	private function importSalesRule(){
	
		if($this->getNextSalesRuleFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migrule',$this->getNextSalesRuleFile(), 'executeImportSalesRule');
			$process->getManager()->work();
		}
	
	}
	
	/**
	 * @return void
	 */
	private function importSalesRuleSql(){
	
		if($this->getNextSalesRuleSqlFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migrule',$this->getNextSalesRuleSqlFile(), 'executeImportSalesRuleSql');
			$process->getManager()->work();
		}
	
	}

	/**
	 * @desc Executa a importação das regras de compras
	 * @return void
	 */
	public function executeImportSalesRule($filename){
	
		$listSalesRule = file_get_contents(base64_decode($this->getPath() . 'salesrule/' . $filename));
		
		$helper = Mage::helper('migrule/sales');
		$helper->execute('import', Mage::getVersion(), $listSalesRule);
		
		unlink($this->getPath() . 'salesrule/' . $filename);
		$this->importSalesRule();
	}

	/**
	 * @return void
	 */
	public function executeImportSalesRuleSql($filename){
	
		$localXmlString = file_get_contents(Mage::getRoot() . '/etc/local.xml');
		
		// pega as configurações de acesso ao banco contidas no local.xml
		$localXmlObj = simplexml_load_string($localXmlString, null, LIBXML_NOCDATA);
		$dataConnection = $localXmlObj->global->resources->default_setup->connection;
		
		$host = $dataConnection->host;
		$username = $dataConnection->username;
		$password = $dataConnection->password;
		$dbname = $dataConnection->dbname;
		$pathSql = $this->getPath() . 'salesrulesql/' . $filename;
		
		// executa o comando o sql via linux
		shell_exec("mysql -h {$host} -u {$username} -p{$password} -D {$dbname} < {$pathSql} &");
		
	}

	/**
	 * @desc Retorna um nome de arquivo da pasta de regras de carrinho não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextSalesRuleFile(){
		$file = false;
		$dir = dir($this->getPath() . 'salesrule/');
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}

	/**
	 * @desc Retorna um nome de arquivo da pasta de customers das regras de carrinho não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextSalesRuleSqlFile(){
		$file = false;
		$dir = dir($this->getPath() . 'salesrulesql/');
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
}