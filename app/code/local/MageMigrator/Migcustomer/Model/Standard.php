<?php

class MageMigrator_Migcustomer_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {
	
	private $categories_ids = array();
	private $stores = array();
	
	public function __construct(){
		$this->stores = Mage::app()->getStores(true);
		parent::__construct();
	}
	
	public function export(){
		
		// attributos dos clientes
		$this->exportAttributes();
		
		$collection = array();
		$collectionGroups = array();
		
		
		// exporta os grupos de clientes
		$groups = Mage::getModel('customer/group')->getCollection();
		foreach($groups as $group){
			$collectionGroups[] = $group->getId();
		}
		
		// exporta os clientes
		$customers = Mage::getModel('customer/customer')->getCollection();
		foreach($customers as $customer){
			$collection[] = $customer->getId();
		}
			
		$process = Mage::getModel('migrator/process');
		$process->export('migcustomer', $collection, 'executeExportCustomer');
		$process->export('migcustomer', $collectionGroups, 'executeExportGroup');
		$process->export('migcustomer', null, 'exportAddress');
		$process->getManager()->work();
		
	}
	
	
	public function exportAttributes(){

		$read = Mage::getSingleton('core/resource')->getConnection('core_read');

		$fetch = $read->fetchAll("SELECT * FROM eav_attribute
									WHERE entity_type_id IN (SELECT entity_type_id FROM eav_entity_type
																WHERE entity_type_code = 'customer' OR entity_type_code = 'customer_address')");
		
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
				$fetCatalogAttribute = $read->fetchAll("SELECT * FROM customer_eav_attribute
															WHERE attribute_id = '{$item['attribute_id']}'");
				foreach($fetCatalogAttribute as $att){
					$listCatalogAttribute[$item['attribute_id']] = $att;
				}
			}

		}

		file_put_contents($this->getPath() . 'customer_attribute/eav_attribute.txt', base64_encode(serialize($listEav)));
		file_put_contents($this->getPath() . 'customer_attribute/eav_attribute_option.txt', base64_encode(serialize($listOption)));
		file_put_contents($this->getPath() . 'customer_attribute/eav_attribute_option_value.txt', base64_encode(serialize($listValue)));
		
		// se for uma versão maior que q a 1.4.0.1
		if(version_compare(Mage::getVersion(), '1.4.0.1', '>=')){
			file_put_contents($this->getPath() . 'customer_attribute/customer_eav_attribute.txt', base64_encode(serialize($listCatalogAttribute)));
		}
		
	}

	public function importAttributes(){

		$listEav = unserialize(base64_decode(file_get_contents($this->getPath() . 'customer_attribute/eav_attribute.txt')));
		$listOption = unserialize(base64_decode(file_get_contents($this->getPath() . 'customer_attribute/eav_attribute_option.txt')));
		$listValue = unserialize(base64_decode(file_get_contents($this->getPath() . 'customer_attribute/eav_attribute_option_value.txt')));
		$listCatalogAttribute = unserialize(base64_decode(file_get_contents($this->getPath() . 'customer_attribute/customer_eav_attribute.txt')));

		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');

		$listCampareValues = array();
		
		foreach($listEav as $item){

			$countEav = count($read->fetchAll("SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}'"));
			if(!$countEav){

				$write->query(
						"INSERT INTO eav_attribute SET
							entity_type_id = {$item['entity_type_id']},
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

			$countCatalogAttribute =  count($read->fetchAll("SELECT attribute_id FROM customer_eav_attribute
					WHERE attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}' LIMIT 1)"));
			if(!$countCatalogAttribute){
				
				if(file_exists($this->getPath() . 'customer_attribute/catalog_eav_attribute.txt')){

					$att = $listCatalogAttribute[$item['attribute_id']];
					$write->query(
						"INSERT INTO customer_eav_attribute SET
							attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = '{$item['attribute_code']}' LIMIT 1),
							is_visible = '{$att['is_visible']}',
							input_filter = '{$att['input_filter']}',
							validate_rules = 'a:2:{s:15:\"max_text_length\";i:{$att['max_text_length']};s:15:\"min_text_length\";i:{$att['min_text_length']};}'
					");
					
				}else{
					
					$write->query(
						"INSERT INTO customer_eav_attribute SET
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
		file_put_contents($this->getPath() . 'customer_attribute/eav_attribute_compare.txt', base64_encode(serialize($listCampareValues)));

	}
	
	
	public function exportAddress($data = null){
		
		$collectionAddresses = array();
		
		// exporta os endereços
		$addresses = Mage::getModel('customer/address')->getCollection();
		foreach($addresses as $address){
			$collectionAddresses[] = $address->getId();
		}
		
		$process = Mage::getModel('migrator/process');
		$process->export('migcustomer', $collectionAddresses, 'executeExportAddress');
		$process->getManager()->work();
		
	}
	
	public function import(){
		
		// attributos dos clientes
		$this->importAttributes();

		// inicia o processo de importação
		$this->executeImportGroup();
		
	}
	
	public function getPath(){
		return Mage::getRoot() . '/../migrator/export/customer/';
	}
	
	public function executeExportCustomer($collection){
		try{
		
			$customers = $collection;
			
			$listCustomers = array();
		
			foreach ($customers as $customerId){
		
				$obj = Mage::getModel('customer/customer')->load($customerId);
				$listCustomers[$customerId] = $obj;
				
			}
			
			/* cria valor randomico */
			$listChars = array(1,2,3,4,5,6,7,8,9);
			$rand = implode('', array_rand($listChars,2));
			
			$filename = microtime(true) . $rand . '.txt';
			
			// salva os dados num arquivo serializado
			file_put_contents($this->getPath() . 'customer/' . $filename, base64_encode(serialize($listCustomers)));
		
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
	}
	
	public function executeExportGroup($collection){
		try{
		
			$groups = $collection;
			
			$listGroups = array();
		
			foreach ($groups as $groupId){
		
				$obj = Mage::getModel('customer/group')->load($groupId);
				$listGroups[$groupId] = $obj->getData();
				
			}
			
			/* cria valor randomico */
			$listChars = array(1,2,3,4,5,6,7,8,9);
			$rand = implode('', array_rand($listChars,2));
			
			$filename = microtime(true) . $rand . '.txt';
			
			// salva os dados num arquivo serializado
			file_put_contents($this->getPath() . 'group/' . $filename, base64_encode(serialize($listGroups)));
		
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
	}
	
	public function executeExportAddress($collection){
		try{

			$listAddresses = array();
		
			foreach($collection as $address){
				
				$loadAddress = Mage::getModel('customer/address')->load($address);
				$listAddresses[$address] = $loadAddress;
			
			}
			
			/* cria valor randomico */
			$listChars = array(1,2,3,4,5,6,7,8,9);
			$rand = implode('', array_rand($listChars,2));
			
			$filename = microtime(true) . $rand . '.txt';
			
			// salva os dados num arquivo serializado
			file_put_contents($this->getPath() . 'address/' . $filename, base64_encode(serialize($listAddresses)));
		
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
	}
	
	public function executeImportGroup(){
		try{
		
			// importa os grupos de cliente
			$dirgroup = dir($this->getPath() . 'group/');
			while(false !== ($entry = $dirgroup->read())){
				if(preg_match('/\.txt$/',$entry)){
					
					$serialData = unserialize(base64_decode(file_get_contents($this->getPath() . 'group/' . $entry)));
					
					foreach ($serialData as $data){
						
						$dados = $data;
						$load = Mage::getModel('customer/group')->load($dados['customer_group_id']);
						if($load->getData('customer_group_code')){
							continue;
						}
						
						$obj = Mage::getModel('customer/group');
						$obj->addData($data);
						$obj->save();
					}
					
					unlink($this->getPath() . 'group/' . $entry);
				}
			}
			
			$this->executeImportCustomer();
		
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
	}
	
	public function executeImportCustomer(){
		
		if($this->getNextCustomerFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migcustomer',$this->getNextCustomerFile(), 'executeImportCustomerCollection');
			$process->getManager()->work();
		}else{
			$this->executeImportCustomerAddress();
		}
		
	}
	
	/**
	 * @desc Retorna um nome de arquivo da pasta de customers se o não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextCustomerFile(){
		$dir = dir($this->getPath() . 'customer/');
		$file = false;
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
	/**
	 * @desc Retorna um nome de arquivo da pasta de address se o não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextCustomerAddressFile(){
		$dir = dir($this->getPath() . 'address/');
		$file = false;
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
	public function executeImportCustomerCollection($collection){
		try{
			
			$serialData = unserialize(base64_decode(file_get_contents($this->getPath() . 'customer/' . $collection)));
			
			foreach ($serialData as $data){

				$log = null;
				if(file_exists($this->getPath() . 'log_clientes.txt')){
					$log .= file_get_contents($this->getPath() . 'log_clientes.txt');
				}
				
				$obj = Mage::getModel('customer/customer');
				
				$dados = $data->getData();
				$load = Mage::getModel('customer/customer')->load($dados['entity_id']);
				if($load->getData('email')){
					
					$log .= "\n" . $dados['entity_id'] . " - Email já cadastrado \n";
					file_put_contents($this->getPath() . 'log_clientes.txt', $log);
					continue;
				
				}
				
				$ldCollection = Mage::getModel('customer/customer')->getCollection()->addAttributeToFilter('email', $dados['email']);
				if(count($ldCollection) > 0){
					continue;
				}
				
				if($dados['email']){
					$obj->addData($dados);
					
					// se houver uma lista de comparação de attributos
					if(file_exists($this->getPath() . 'customer_attribute/eav_attribute_compare.txt')){
						$listCompareValues = unserialize(base64_decode(file_get_contents($this->getPath() . 'customer_attribute/eav_attribute_compare.txt')));
					
						// nova lista de attributos
						$newData = array();
					
						// percorre os attributos dos clientes
						foreach($dados as $attribute => $value){

							if(array_key_exists($attribute, $listCompareValues)){
								if(array_key_exists($value, $listCompareValues[$attribute])){
					
									// adiciona um novo valor ao attributo
									$newData[$attribute] = $listCompareValues[$attribute][$value];
					
								}
							}
					
						}
					
						if(!empty($newData)){
							$obj->addData($newData);
						}
					
					}
					
					$obj->save();
				}else{
					$log .= "\n" . $dados['entity_id'] . " - Email não foi preenchido \n";
					file_put_contents($this->getPath() . 'log_clientes.txt', $log);
				}
				
			}
			
			unlink($this->getPath() . 'customer/' . $collection);
			$this->executeImportCustomer();
		
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
		
	}
	
	public function executeImportCustomerAddress(){
		
		if($this->getNextCustomerAddressFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migcustomer',$this->getNextCustomerAddressFile(), 'executeImportAddress');
			$process->getManager()->work();
		}
		
	}
	
	public function executeImportAddress($collection){
		try{

			$serialData = unserialize(base64_decode(file_get_contents($this->getPath() . 'address/' . $collection)));
			
			foreach ($serialData as $data){
			
				$dados = $data->getData();
				$load = Mage::getModel('customer/address')->load($dados['entity_id']);
				if($load->getData('postcode')){
					continue;
				}
				
				$customer = Mage::getModel('customer/customer')->load($dados['parent_id']);
				if($customer->getData('email')){
				
					$obj = Mage::getModel('customer/address');
					$obj->addData($data->getData());
					
					// se houver uma lista de comparação de attributos
					if(file_exists($this->getPath() . 'customer_attribute/eav_attribute_compare.txt')){
						$listCompareValues = unserialize(base64_decode(file_get_contents($this->getPath() . 'customer_attribute/eav_attribute_compare.txt')));
					
						// nova lista de attributos
						$newData = array();
					
						// percorre os attributos dos clientes
						foreach($data->getData() as $attribute => $value){
					
							if(array_key_exists($attribute, $listCompareValues)){
								if(array_key_exists($value, $listCompareValues[$attribute])){
					
									// adiciona um novo valor ao attributo
									$newData[$attribute] = $listCompareValues[$attribute][$value];
					
								}
							}
					
						}
					
						if(!empty($newData)){
							$obj->addData($newData);
						}
					
					}
					
					$obj->save();
					
				}else{
					
					$log = null;
					
					if(file_exists($this->getPath() . 'log_address.txt')){
						$log .= file_get_contents($this->getPath() . 'log_address.txt');
					}
					
					$log .= "\n\n" . $dados['entity_id'] . ' - Email não foi preenchido';
					file_put_contents($this->getPath() . 'log_address.txt', $log);
					
				}
			
			}
			
			unlink($this->getPath() . 'address/' . $collection);
			$this->executeImportCustomerAddress();
			
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
	}
	
}