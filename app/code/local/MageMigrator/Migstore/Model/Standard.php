<?php

class MageMigrator_Migstore_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {

	public function export(){
		
		// Inicia a exportação
		$storeCollection = Mage::getModel('core/store')->getCollection();
		$storeGroupCollection = Mage::getModel('core/store_group')->getCollection();
		$storeWebsiteCollection = Mage::getModel('core/website')->getCollection();
		
		$listStores = array();
		$listStoresWebsites = array();
		$listStoresGroups = array();
		
		foreach($storeCollection as $store){
			
			$ldStore = Mage::getModel('core/store')->load($store->getId());
			$listStores[] = $ldStore->getData();
			
		}
		
		foreach($storeGroupCollection as $store){
			
			$ldStoreGroup = Mage::getModel('core/store_group')->load($store->getId());
			$listStoresGroups[] = $ldStoreGroup->getData();
			
		}
		
		foreach($storeWebsiteCollection as $store){
			
			$ldStoreWebsite = Mage::getModel('core/website')->load($store->getId());
			$listStoresWebsites[] = $ldStoreWebsite->getData();
			
		}
		
		// salva os dados num arquivo serializado
		file_put_contents($this->getPath() . 'stores.txt', base64_encode(serialize($listStores)));
		file_put_contents($this->getPath() . 'stores_website.txt', base64_encode(serialize($listStoresWebsites)));
		file_put_contents($this->getPath() . 'stores_group.txt', base64_encode(serialize($listStoresGroups)));
		
	}
	
	public function import(){
		try{
		
			Mage::registry('isSecureArea');
		
			$listStores = unserialize(base64_decode(file_get_contents($this->getPath() . 'stores.txt')));
			$listStoresWebsite = unserialize(base64_decode(file_get_contents($this->getPath() . 'stores_website.txt')));
			$listStoresGroup = unserialize(base64_decode(file_get_contents($this->getPath() . 'stores_group.txt')));
			
			foreach($listStoresWebsite as $store){
				
				$loadStoreWebsite = Mage::getModel('core/website')->load($store['website_id']);
				if($loadStoreWebsite->getName()){

					$newStoreWebsite = Mage::getModel('core/website');
					$newStoreWebsite->setData($store);
					$newStoreWebsite->save();
				
				}else{
					
					$write = Mage::getSingleton('core/resource')->getConnection('write');
					$write->query("
						INSERT INTO core_website SET
							website_id = {$store['website_id']},
							code = '{$store['code']}',		
							name = '{$store['name']}',		
							sort_order = '{$store['sort_order']}',		
							default_group_id = '{$store['default_group_id']}',		
							is_default = '{$store['is_default']}'		
					");
					
				}
				
			}
			
			foreach($listStoresGroup as $store){
				
				$loadStoreGroup = Mage::getModel('core/store_group')->load($store['group_id']);
				if($loadStoreGroup->getName()){
				
					$newStoreGroup = Mage::getModel('core/store_group');
					$newStoreGroup->setData($store);
					$newStoreGroup->save();
				
				}else{
				
					$write = Mage::getSingleton('core/resource')->getConnection('write');
					$write->query("
						INSERT INTO core_store_group SET
							group_id = {$store['group_id']},
							website_id = {$store['website_id']},
							name = '{$store['name']}',
							root_category_id = '{$store['root_category_id']}',
							default_store_id = '{$store['default_store_id']}'
					");
				
				}
	
			}
			
			foreach($listStores as $store){
			
				$loadStore = Mage::getModel('core/store')->load($store['store_id']);
				if($loadStore->getName()){
				
					$newStore = Mage::getModel('core/store');
					$newStore->setData($store);
					$newStore->save();
				
				}else{
				
					$write = Mage::getSingleton('core/resource')->getConnection('write');
					$write->query("
						INSERT INTO core_store SET
							store_id = {$store['store_id']},
							group_id = {$store['group_id']},
							website_id = {$store['website_id']},
							code = '{$store['code']}',
							name = '{$store['name']}',
							sort_order = '{$store['sort_order']}',
							is_active = '{$store['is_active']}'
					");
				
				}
				
			}
			
		}catch(Exception $e){
			echo $e->getMessage();
			die;		
		}
		
	}
	
	public function getPath(){
		return Mage::getRoot() . '/../migrator/export/stores/';
	}
		
}