<?php

class MageMigrator_Migproduct_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {
	
	private $categories_ids = array();
	private $stores = array();
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::export()
	 */
	public function export(){
		
		// exporta os attributos dos produtos
		$this->exportAttributes();
		
		// exporta os produtos
		$this->exportProducts();
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::import()
	 */
	public function import(){
		
		// inicia a importação dos atributos
		$this->importAttributes();
		
		// inicia importação os produtos
		$this->importProducts();
		
	}
	
	/**
	 * @desc Retorna string com o caminho para as imagens dos produtos na pasta media
	 * @return string
	 */
	public function getPath(){
		return Mage::getRoot() . '/../migrator/export/catalog/';
	}
	
	/**
	 * @desc Retorna string com o caminho para as imagens dos produtos na pasta media
	 * @return string
	 */
	public function getPathMedia(){
		$base = dirname(Mage::getRoot());
		return $base . '/media/catalog/product/';
	}
	
	/**
	 * @desc Inicia o processo de exportar os objetos que envolvem os produtos do sistema
	 */
	private function exportProducts(){
		
		
		$path = Mage::getBaseUrl() . 'migrator/api/catalog_product.php';
		$pLog = Mage::getRoot() . '/../migrator/log/log.txt';
		$data = "method=export";
		shell_exec("curl --data \"{$data}\" {$path} > {$pLog} &");
		
		$process = Mage::getModel('migrator/process');
		$process->export('migproduct', null, 'executeExportLinkProducts');
		$process->getManager()->work();
		
	}
	
	public function exportAttributes(){
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		
		$fetch = $read->fetchAll("SELECT * FROM eav_attribute 
									WHERE entity_type_id = (SELECT entity_type_id FROM eav_entity_type 
																	WHERE entity_type_code = 'catalog_product')");
		$listEav = array();
		$listOption = array();
		$listValue = array();
		$listCatalogAttribute = array();
		
		foreach($fetch as $item){
			$listEav[] = $item;
			
			// pega todas as options do atributo
			$fetOption = $read->fetchAll("SELECT * FROM eav_attribute_option
											 WHERE attribute_id = '{$item['attribute_id']}'");
			
			foreach($fetOption as $opt){
				$listOption[$item['attribute_id']][] = $opt;
				
				// pega todos valores da option
				$fetValue = $read->fetchAll("SELECT * FROM eav_attribute_option_value
											 	WHERE option_id = '{$opt['option_id']}'");
				
				foreach($fetValue as $vle){
					$listValue[$opt['option_id']][] = $vle;
				}
				
			}

			// se for uma versão maior que q a 1.4.0.1
			if(version_compare(Mage::getVersion(), '1.4.0.1', '>=')){
				// pega o registro do atributo no catalogo
				$fetCatalogAttribute = $read->fetchAll("SELECT * FROM catalog_eav_attribute
															WHERE attribute_id = '{$item['attribute_id']}'");
				foreach($fetCatalogAttribute as $att){
					$listCatalogAttribute[$item['attribute_id']] = $att;
				}
			}
			
		}
		
		file_put_contents($this->getPath() . 'product_attribute/eav_attribute.txt', base64_encode(serialize($listEav)));
		file_put_contents($this->getPath() . 'product_attribute/eav_attribute_option.txt', base64_encode(serialize($listOption)));
		file_put_contents($this->getPath() . 'product_attribute/eav_attribute_option_value.txt', base64_encode(serialize($listValue)));
		
		// se for uma versão maior que q a 1.4.0.1
		if(version_compare(Mage::getVersion(), '1.4.0.1', '>=')){
			file_put_contents($this->getPath() . 'product_attribute/catalog_eav_attribute.txt', base64_encode(serialize($listCatalogAttribute)));
		}
		
	}
	
	public function importAttributes(){
		
		$listEav = unserialize(base64_decode(file_get_contents($this->getPath() . 'product_attribute/eav_attribute.txt')));
		$listOption = unserialize(base64_decode(file_get_contents($this->getPath() . 'product_attribute/eav_attribute_option.txt')));
		$listValue = unserialize(base64_decode(file_get_contents($this->getPath() . 'product_attribute/eav_attribute_option_value.txt')));
		
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		
		$listCampareValues = array();
		$listAttributesIds = array();
		
		foreach($listEav as $item){
			
			$countEav = count($read->fetchAll("SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}'"));
			if(!$countEav){
				
				$write->query(
				"INSERT INTO eav_attribute SET
						entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product'),
						attribute_code = '{$item['attribute_code']}',
						attribute_model = '{$item['attribute_model']}',
						backend_model = '{$item['backend_model']}',
						backend_type = '{$item['backend_type']}',
						backend_table = '{$item['backend_table']}',
						frontend_model = '{$item['frontend_model']}',
						frontend_input = '{$item['frontend_input']}',
						frontend_label = '{$item['frontend_label']}',
						frontend_class = '{$item['frontend_class']}',
						source_model = '{$item['source_model']}',
						is_required = '{$item['is_required']}',
						is_user_defined = '{$item['is_user_defined']}',
						default_value = '{$item['default_value']}',
						is_unique = '{$item['is_unique']}',
						note = '{$item['note']}'
				");
			
			}
			
			// adiciona o attribute na listagem dos ids de attributos
			$fetchAttributeId = $read->fetchAll("SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}'");
			$listAttributesIds[$item['attribute_id']] = $fetchAttributeId[0]['attribute_id'];
			
			$countCatalogAttribute =  count($read->fetchAll("SELECT attribute_id FROM catalog_eav_attribute
					WHERE attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}' LIMIT 1)"));
			
			if(!$countCatalogAttribute){
				
				if(file_exists($this->getPath() . 'product_attribute/catalog_eav_attribute.txt')){
					
					$listCatalogAttribute = unserialize(base64_decode(file_get_contents($this->getPath() . 'product_attribute/catalog_eav_attribute.txt')));
					
					$att = $listCatalogAttribute[$item['attribute_id']];
					$write->query(
					"INSERT INTO catalog_eav_attribute SET
						attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}' LIMIT 1),
						frontend_input_renderer = '{$att['frontend_input_renderer']}',
						is_visible = '{$att['is_visible']}',
						is_global = '{$att['is_global']}',
						is_searchable = '{$att['is_searchable']}',
						is_filterable = '{$att['is_filterable']}',
						is_comparable = '{$att['is_comparable']}',
						is_visible_on_front = '{$att['is_visible_on_front']}',
						is_html_allowed_on_front = '{$att['is_html_allowed_on_front']}',
						is_used_for_price_rules = '{$att['is_used_for_price_rules']}',
						is_filterable_in_search = '{$att['is_filterable_in_search']}',
						used_in_product_listing = '{$att['used_in_product_listing']}',
						used_for_sort_by = '{$att['used_for_sort_by']}',
						is_configurable = '{$att['is_configurable']}',
						apply_to = '{$att['apply_to']}',
						is_visible_in_advanced_search = '{$att['is_visible_in_advanced_search']}',
						position = '{$att['position']}',
						is_wysiwyg_enabled = '{$att['is_wysiwyg_enabled']}'
					");
					
				}else{
					
					$write->query(
					"INSERT INTO catalog_eav_attribute SET
						attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}' LIMIT 1)
					");
					
				}
				
			}
			
			if(!array_key_exists($item['attribute_id'],$listOption)){
				continue;
			}
			
			$listCampareValues[$item['attribute_code']] = array();
			
			// apaga todas as opções desse atributo
			$write->query(
			"DELETE FROM eav_attribute_option WHERE
				attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}' LIMIT 1)
			");
			foreach($listOption[$item['attribute_id']] as $opt){
				
				$write->query(
				"INSERT INTO eav_attribute_option SET
					attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}' LIMIT 1),
					sort_order = '{$opt['sort_order']}'
				");
				
				$eavAttOption = $read->fetchAll("SELECT option_id FROM eav_attribute_option ORDER BY option_id DESC");
				$listCampareValues[$item['attribute_code']][$opt['option_id']] = $eavAttOption[0]['option_id'];
				
				if(!array_key_exists($opt['option_id'],$listValue)){
					continue;
				}
				
				foreach($listValue[$opt['option_id']] as $vle){
					$value = addslashes($vle['value']);
					$write->query(
					"INSERT INTO eav_attribute_option_value SET
						option_id = (SELECT option_id FROM eav_attribute_option ORDER BY option_id DESC LIMIT 1),
						store_id = '{$vle['store_id']}',
						value = '{$value}'
					");
				
				}
				
			}
			
			
		}
		
		// salva a lista de comparação para que possa ser usada no cadastro dos produtos
		file_put_contents($this->getPath() . 'product_attribute/eav_attribute_compare.txt', base64_encode(serialize($listCampareValues)));
		
		// salva os ids dos attributos
		file_put_contents($this->getPath() . 'product_attribute/eav_attribute_ids.txt', base64_encode(serialize($listAttributesIds)));
		
	}
	
	/**
	 * @desc Efetua a exportação dos produtos e seus relacionamentos
	 * @return void
	 */
	public function executeExportProducts($collection, $filename){
		
		$path = Mage::getBaseUrl() . 'migrator/api/catalog_product.php';
		$pLog = Mage::getRoot() . '/../migrator/log/log.txt';
		$data = "method=executeExportProducts&collection={$filename}";
		shell_exec("curl --data \"{$data}\" {$path} > {$pLog} &");
		
	}
	
	/**
	 * @desc Efetua a exportação dos relacionamentos dos produtos
	 * @return void
	 */
	public function executeExportLinkProducts($collection = null){
		try{
			
			/* cria valor randomico */
			$listChars = array(1,2,3,4,5,6,7,8,9);
			$rand = implode('', array_rand($listChars,2));
			
			$filename = microtime(true) . $rand . '.txt';
			
			$link = Mage::getModel('catalog/product_link')->getCollection();
			file_put_contents($this->getPath() . 'product_link/' . $filename, base64_encode(serialize($link->getData())));
		
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
	}
	
	/**
	 * @desc Dispara processo de importação dos produtos
	 * @return void
	 */
	public function importProducts(){
	
		if($this->getNextProductFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migproduct', $this->getNextProductFile(), 'executeImportProducts');
			$process->getManager()->work();
		}else{
			// importa as opções personalizadas
			$this->executeImportOptions();
			
			// importa as ligações entre os produtos
			$this->importLinkProducts();
		}
		
	}
	
	/**
	 * @desc Executa a importação das opções personalizadas dos produtos
	 * @return void
	 */
	public function executeImportOptions(){
		
		$options = unserialize(base64_decode(file_get_contents($this->getPath() . 'product_options.txt')));
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		$tables = array(
			'options' => 'catalog_product_option',
			'optionsTypeValue' => 'catalog_product_option_type_value',
			'optionsPrices' => 'catalog_product_option_price',
			'optionsTitles' => 'catalog_product_option_title',
			'optionsTypePrice' => 'catalog_product_option_type_price',
			'optionsTypeTitle' => 'catalog_product_option_type_title',
		);
		
		$write->query('DELETE FROM catalog_product_option');
		foreach($tables as $key => $table){
			$op = $options[$key];
			if(!empty($op)){
				foreach($op as $item){
					
					$sqlInsert = "INSERT INTO {$tables[$key]} SET ";
					$count = 0;
					foreach($item as $column => $value){
						
						if($count > 0){
							$sqlInsert .= ', ';
						}
						$value = addslashes($value);
						$sqlInsert .= "{$column} = '{$value}'";
						$count++;
						
					}
					
					$write->query($sqlInsert);
				}
			}
		}
		
	}
	
	/**
	 * @desc Efetua a importação dos produtos para o sistema
	 * @return void
	 */
	public function executeImportProducts($filename){
		
		$path = "http://{$_SERVER['HTTP_HOST']}/migrator/api/catalog_product.php";
		$pLog = Mage::getRoot() . '/../migrator/log/log.txt';
		$data = "method=executeImportProducts&collection=".$filename;
		shell_exec("curl --data \"{$data}\" {$path} > {$pLog} &");
		
	}
	
	/**
	 * @desc Dispara processo de importação dos relacionamentos de produtos
	 * @return void
	 */
	private function importLinkProducts(){
	
		if($this->getNextLinkProductFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migproduct',$this->getNextLinkProductFile(), 'executeImportLinkProducts');
			$process->getManager()->work();
		}else{
			$process = Mage::getModel('migrator/process');
			$process->import('migproduct',null, 'executeImportSuperProducts');
			$process->getManager()->work();
		}
	
	}
	
	/**
	 * @desc Efetua a importação dos relacionamento dos produtos para o sistema
	 * @return void
	 */
	public function executeImportLinkProducts($filename){
		
		$links = unserialize(base64_decode(file_get_contents($this->getPath() . 'product_link/' . $filename)));
		
		foreach($links as $link){
			unset($link['link_id']);
			
			$load = Mage::getModel('catalog/product')->load($link['product_id']);
			if(!$load->getSku()){
				continue;
			}
			
			$modelLink = Mage::getModel('catalog/product_link');
			$modelLink->setData($link);
			$modelLink->save();
			
		}
		
		unlink($this->getPath() . 'product_link/' . $filename);
		$this->importLinkProducts();
	
	}
	
	/**
	 * @desc Efetua a importação das configurações dos produtos configuraveis
	 * @return void
	 */
	public function executeImportSuperProducts(){
		
		$listSuper = unserialize(base64_decode(file_get_contents($this->getPath() . 'product_super.txt')));
		$listAttributeIds = unserialize(base64_decode(file_get_contents($this->getPath() . 'product_attribute/eav_attribute_ids.txt')));
		
		foreach($listSuper as $table => $fetch){
			foreach($fetch as $data){
				
				// monta o insert do item
				$sql = " INSERT INTO {$table} SET ";
				$separator = '';
				foreach($data as $column => $value){
					
					if($table == 'catalog_product_super_attribute' && $column == 'attribute_id'){
						$value = $listAttributeIds[$value];
					}
					
					$sql .= $separator . "{$column} = '{$value}'";
					$separator = ', ';
				}
				
				$this->executeQuery($sql);
				
			}
		}
		
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
	 * @desc Retorna um nome de arquivo da pasta de produtos se o nÃ£o existir nada retorna false
	 * @return string|bool
	 */
	public function getNextProductFile(){
		$dir = dir($this->getPath() . 'product/');
		$file = false;
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
	/**
	 * @desc Retorna um nome de arquivo da pasta de relacionamento de produtos se o não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextLinkProductFile(){
		$file = false;
		$dir = dir($this->getPath() . 'product_link/');
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
}