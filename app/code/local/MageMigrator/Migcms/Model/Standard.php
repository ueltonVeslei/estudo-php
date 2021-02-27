<?php

class MageMigrator_Migcms_Model_Standard extends MageMigrator_Migrator_Model_Type_Abstract {

	public function export(){
		
		// Inicia a exportação
		$pagesCollection = Mage::getModel('cms/page')->getCollection();
		$blocksCollection = Mage::getModel('cms/block')->getCollection();
		
		$listPages = array();
		$listBlocks = array();
		
		foreach($pagesCollection as $page){
			
			$ld = Mage::getModel('cms/page')->load($page->getId());
			$listPages[] = $ld->getData();
			
		}
		
		foreach($pagesCollection as $block){
			
			$ld = Mage::getModel('cms/block')->load($block->getId());
			$listBlocks[] = $ld->getData();
			
		}
		
		// salva os dados num arquivo serializado
		file_put_contents($this->getPath() . 'cms_pages.txt', base64_encode(serialize($listPages)));
		file_put_contents($this->getPath() . 'cms_blocks.txt', base64_encode(serialize($listBlocks)));
	}
	
	public function import(){
		
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$listPages = unserialize(base64_decode(file_get_contents($this->getPath() . 'cms_pages.txt')));
		$listBlocks = unserialize(base64_decode(file_get_contents($this->getPath() . 'cms_blocks.txt')));
		
		foreach($listPages as $page){
			
			unset($page['page_id']);
			$new = Mage::getModel('cms/page');
			$new->setData($page);
			$new->save();
			
		}
		
		foreach($listBlocks as $block){
			
			unset($block['group_id']);
			$new = Mage::getModel('cms/block');
			$new->setData($block);
			$new->save();

		}
		
	}
	
	public function getPath(){
		return Mage::getRoot() . '/../migrator/export/cms/';
	}
		
}