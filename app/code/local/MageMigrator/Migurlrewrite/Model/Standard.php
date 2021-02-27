<?php 

class MageMigrator_Migurlrewrite_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {
		
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * @desc Retorna string com o caminho onde vão ficar os arquivos serializados
	 * @return string
	 */
	public function getPath(){
		return Mage::getRoot() . '/../migrator/export/urlrewrite/';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::export()
	 */
	public function export(){
		
		$collection = Mage::getModel('core/url_rewrite')->getCollection();
		$listUrlId = array();
		foreach($collection as $url){
			$listUrlId[] = $url->getId();
		}
		
		$process = Mage::getModel('migrator/process');
		$process->export('migurlrewrite', $listUrlId, 'executeExportUrlrewrite');
		$process->getManager()->work();
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::import()
	 */
	public function import(){
		
		$this->importUrlrewrite();
		
	}
	
	/**
	 * @desc Executa o processo exportação das reescritas de url
	 */
	public function executeExportUrlrewrite($collection){
		
		$listUrl = array();
		foreach($collection as $urlId){
			$model = Mage::getModel('core/url_rewrite')->load($urlId);
			$listUrl[] = $model->getData();
		}
		
		/* cria valor randomico */
		$listChars = array(1,2,3,4,5,6,7,8,9);
		$rand = implode('', array_rand($listChars,2));
		
		$filename = microtime(true) . $rand . '.txt';
		
		// salva os dados em um arquivo serializado e criptografado
		file_put_contents($this->getPath() . $filename, base64_encode(serialize($listUrl)));
		
	}

	/**
	 * @desc Inicia o processo importação das reescritas de url
	 */
	public function importUrlrewrite(){
		
		if($this->getNextUrlrewriteFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migurlrewrite', $this->getNextUrlrewriteFile(), 'executeImportUrlrewrite');
			$process->getManager()->work();
		}
		
	}
	
	/**
	 * @desc Executa o processo importação das reescritas de url
	 */
	public function executeImportUrlrewrite($filename){

		$collection = unserialize(base64_decode(file_get_contents($this->getPath() . $filename)));

		foreach($collection as $data){
			
			if($data['category_id'] != ''){
				$category = Mage::getModel('catalog/category')->load($data['category_id']);
				if(!$category->getName()){
					continue;
				}
			}else{
				unset($data['category_id']);
			}
			
			if($data['product_id'] != ''){
				$product = Mage::getModel('catalog/product')->load($data['product_id']);
				if(!$product->getSku()){
					continue;
				}
			}else{
				unset($data['product_id']);
			}
			
			$load = Mage::getModel('core/url_rewrite')->load($data['url_rewrite_id']);
			if($load->getIdPath()){
				continue;
			}
			
			// monta o insert do item
			$sql = " INSERT INTO core_url_rewrite SET ";
			$separator = '';
			foreach($data as $column => $value){
				$sql .= $separator . "{$column} = '{$value}'";
				$separator = ', ';
			}
			
			$this->executeQuery($sql);
			
		}
		
		unlink($this->getPath() . $filename);
		$this->importUrlrewrite();
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
	 * @desc Retorna um nome de arquivo da pasta do url rewrite se o não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextUrlrewriteFile(){
		$file = false;
		$dir = dir($this->getPath());
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
}