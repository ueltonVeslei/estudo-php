<?php

class MageMigrator_Migcatalog_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {
	
	private $categories_ids = array();
	private $stores = array();
	
	//abc	

	public function __construct(){
		//$this->stores = Mage::app()->getStores(true);
		parent::__construct();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::export()
	 */
	public function export(){
		
		// exporta as categorias
		$this->exportCategories();
		
		// exporta os produtos
		//$this->exportProducts();
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see MageMigrator_Migrator_Model_Type_Abstract::import()
	 */
	public function import(){
		
		// inicia o processo de importação
		$this->importCategories();
		
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
	 * @desc Efetua o processo de exportar os objetos que envolvem as categorias do sistema, salvando em um arquivo txt
	 */
	private function exportCategories(){
		
		$process = Mage::getModel('migrator/process');
		
		foreach(Mage::app()->getStores() as $storeId => $store){
			$process->import('migcatalog', $storeId, 'executeExportCategories');
		}
		
		$process->getManager()->work();
		
	}
	
	public function executeExportCategories($storeId){
		
		Mage::app()->setCurrentStore($storeId);
		
		// efetua a exportação das categorias
		$listCategories = array();
		
		$categories = Mage::getModel('catalog/category')->getCollection()->addStoreFilter($storeId);
		
		foreach ($categories as $cat){
		
			$category = Mage::getModel('catalog/category')->load($cat->getId());
			$listCategories[] = $category;
		
		}
		
		// salva as categiras serializadas
		file_put_contents($this->getPath() . "category/catalog_category_".$storeId.".txt", base64_encode(serialize($listCategories)));
		
	}
	
	/**
	 * @desc Inicia o processo de exportar os objetos que envolvem os produtos do sistema
	 */
	private function exportProducts(){
		
		$collection = array();
		//foreach($this->stores as $store){
			
			$products = Mage::getModel('catalog/product')->getCollection();
			
			foreach($products as $prod){
				$collection[$prod->getId()] = $prod->getId();
			}
			
		//}

		$process = Mage::getModel('migrator/process');
		$process->export('migcatalog', $collection, 'executeExportProducts');
		$process->export('migcatalog', null, 'executeExportLinkProducts');
		$process->getManager()->work();
		
	}
	
	/**
	 * @desc Efetua a exportação dos produtos e seus relacionamentos
	 * @return void
	 */
	public function executeExportProducts($collection){
		try{
		
			$products = $collection;
			
			$listProducts = array();
			$listProductsConfigs = array();
		
			foreach ($products as $prod){
		
				$obj = Mage::getModel('catalog/product')->load($prod);
				$listProducts[$prod] = $obj;
				$listProductsConfigs[$prod] = array(
						'categories' => $obj->getCategoryIds(),
						'stores' => $obj->getStoreIds(),
						'websites' => $obj->getWebsiteIds(),
				);
		
			}
			
			/* cria valor randomico */
			$listChars = array(1,2,3,4,5,6,7,8,9);
			$rand = implode('', array_rand($listChars,2));
			
			$filename = microtime(true) . $rand . '.txt';
			
			// salva as produtos serializadas
			file_put_contents($this->getPath() . 'product/' . $filename, serialize($listProducts));
			file_put_contents($this->getPath() . 'product_config/' . $filename, serialize($listProductsConfigs));	
		
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
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
			file_put_contents($this->getPath() . 'product_link/' . $filename, serialize($link->getData()));
		
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
	}
	
	/**
	 * @desc Dispara processo de importação das categorias e inicia processo de importação dos produtos
	 * @return void
	 */
	private function importCategories(){
		
		$files = array();
		$dir = dir($this->getPath() . 'category/');
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$files[] = unserialize(base64_decode(file_get_contents($this->getPath() . 'category/' . $entry)));
			}
		}
		
		$categories = array();
		foreach($files as $arrCategories){
			foreach($arrCategories as $category){
				$categories[] = $category;
			}
		}
		
		$this->executeImportCategoriesStore($categories);
		
		// importa os produtos
		//$this->importProducts();

	}
	
	public function executeImportCategories($filename){

		$data = base64_decode(file_get_contents($this->getPath() . 'category/' . $filename));
		$categories = unserialize($data);
		$this->executeImportCategoriesStore($categories);
		
		unlink($this->getPath() . 'category/' . $filename);
		$this->importCategories();
	}
	
	/**
	 * @desc Efetua a importação das categorias para o sistema
	 * @return void
	 */
	public function executeImportCategoriesStore($categories){
		
		try{
			$caKey = 0;
			$caFinal = (count($categories) - 1);
			while($caKey <= $caFinal){
				
				$category = $categories[$caKey];
				
				$data = $category->getData();
				
				if($data['parent_id'] > 0){
					if(!in_array($data['parent_id'], $this->categories_ids)){
						$caKey++;
						continue;
					}
				}
				
				if(in_array($data['entity_id'], $this->categories_ids)){
					$caKey++;
					continue;
				}
				
				$load = Mage::getModel('catalog/category')->load($data['entity_id']);
				if($load->getName()){
					$caKey++;
					continue;
				}
				
				$model = Mage::getModel('catalog/category');
				$model->setData($data);
				$model->save();
				
				$this->categories_ids[] = $data['entity_id'];
	
				if(count($this->categories_ids) < $caFinal){
					$this->executeImportCategories($categories);
				}
				
				$caKey++;
			}
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
		
	}
	
	/**
	 * @desc Dispara processo de importação dos produtos
	 * @return void
	 */
	private function importProducts(){
	
		if($this->getNextProductFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migcatalog',$this->getNextProductFile(), 'executeImportProducts');
			$process->getManager()->work();
		}else{
			$this->importLinkProducts();
		}
		
	}
	
	/**
	 * @desc Efetua a importação dos produtos para o sistema
	 * @return void
	 */
	public function executeImportProducts($filename){
		
		try{
		
			$products = unserialize(file_get_contents($this->getPath() . 'product/' . $filename));
			$config = unserialize(file_get_contents($this->getPath() . 'product_config/' . $filename));
			
			foreach($products as $prKey => $product){
				
				$data = $product->getData();
				unset($data['media_gallery']);
		
				$load = Mage::getModel('catalog/product')->load($data['entity_id']);
				if($load->getSku()){
					continue;
				}
				
				$model = Mage::getModel('catalog/product');
				$model->addData($data);
		
				$model->setCategoryIds($config[$prKey]['categories']);
				$model->setWebsiteIds($config[$prKey]['websites']);
				$model->setStoreIds($config[$prKey]['stores']);
				
				$gallery = $product->getMediaGallery();
				if(!empty($gallery['images'])){
					
					foreach($gallery['images'] as $key => $value){
						
						$typeImage = array();
						
						if(isset($data['small_image'])){
							if($value['file'] == $data['small_image']){
								$typeImage[] = 'small_image';
							}
						}
						
						if(isset($data['image'])){
							if($value['file'] == $data['image']){
								$typeImage[] = 'image';
							}
						}
						
						if(isset($data['thumbnail'])){
							if($value['file'] == $data['thumbnail']){
								$typeImage[] = 'thumbnail';
							}
						}
						
						if(file_exists($this->getPathMedia() . $value['file'])){
							$model->addImageToMediaGallery($this->getPathMedia() . $value['file'],((empty($typeImage))?null:$typeImage),true,false);
						}
						
					}
					
				}
				
				$model->save();
				
				// monta o stock
				$stock = $product->getStockItem()->getData();
				unset($stock['item_id']);
				$stockItem = Mage::getModel('cataloginventory/stock_item')
				->setData($stock)
				->save();
				
			}
			
			
			if(unlink($this->getPath() . 'product/' . $filename)){
				unlink($this->getPath() . 'product_config/' . $filename);
			}
			
			$this->importProducts();
		
		} catch(Exception $e){
			echo $e->getMessage();
			die;
		}
		
	}
	
	/**
	 * @desc Dispara processo de importação dos relacionamentos de produtos
	 * @return void
	 */
	private function importLinkProducts(){
	
		if($this->getNextLinkProductFile()){
			$process = Mage::getModel('migrator/process');
			$process->import('migcatalog',$this->getNextLinkProductFile(), 'executeImportLinkProducts');
			$process->getManager()->work();
		}
	
	}
	
	/**
	 * @desc Efetua a importação dos relacionamento dos produtos para o sistema
	 * @return void
	 */
	public function executeImportLinkProducts($filename){
		
		$links = unserialize(file_get_contents($this->getPath() . 'product_link/' . $filename));
		
		foreach($links as $link){
			unset($link['link_id']);
			$modelLink = Mage::getModel('catalog/product_link');
			$modelLink->setData($link);
			$modelLink->save();
		}
		
		unlink($this->getPath() . 'product_link/' . $filename);
		$this->importLinkProducts();
	
	}
	
	/**
	 * @desc Retorna um nome de arquivo da pasta de produtos se o não existir nada retorna false
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
	
	/**
	 * @desc Retorna um nome de arquivo da pasta de categorias se o não existir nada retorna false
	 * @return string|bool
	 */
	public function getNextCategoryFile(){
		$file = false;
		$dir = dir($this->getPath() . 'category/');
		while(false !== ($entry = $dir->read())){
			if(preg_match('/\.txt$/',$entry)){
				$file = $entry;
			}
		}
		return $file;
	}
	
}