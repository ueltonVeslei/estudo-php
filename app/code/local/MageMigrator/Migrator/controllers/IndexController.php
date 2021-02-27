<?php

class MageMigrator_Migrator_IndexController extends Mage_Core_Controller_Front_Action {
	
	public function exportAction(){
		
		$this->loadLayout();
		
		$this->getLayout()->getBlock('content')->setTemplate('migrator/export.phtml');
		
		$this->renderLayout();
		
		if($this->getRequest()->isPost()){

			$post = $this->getRequest()->getPost('setors');
			
			if(empty($post)){
				$redirect = 'migrator/index/import/code/1';
			}
				
			// instancia os setores
			foreach ($post as $setor){
				
				if(!$this->getStandard()->isValidSetor( $setor )){
					continue;	
				}
				
				$model = Mage::getModel($setor . '/standard');
				
				if($model instanceof MageMigrator_Migrator_Model_Type_Abstract){
					$model->export();
				}

				$redirect = 'migrator/index/finally';
			}
			
			$this->_redirect($redirect);
			
		}
		
	}
	
	public function importAction(){
		
		$this->loadLayout()->renderLayout();
		
		if($this->getRequest()->isPost()){

			$post = $this->getRequest()->getPost();
			
			if(empty($post['setors'])){
				$redirect = 'migrator/index/import/code/1';
			}
				
			// instancia os setores
			foreach ($post['setors'] as $setor){
				
				if(!$this->getStandard()->isValidSetor( $setor )){
					continue;	
				}
				
				$model = Mage::getModel($setor . '/standard');
				
				if($model instanceof MageMigrator_Migrator_Model_Type_Abstract){
					$model->import();
				}

				$redirect = 'migrator/index/finally';
			}
			
			$this->_redirect($redirect);
				
		}
		
	}
	
	public function backgroundExportAction(){
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$model = $this->getRequest()->getParam('model');
		$filename = $this->getRequest()->getParam('collection');
		$method = $this->getRequest()->getParam('method');
		
		$collection = unserialize(file_get_contents(Mage::getRoot() . '/../migrator/export/collections/' . $filename));
		
		$modelInstance = Mage::getModel($model . '/standard');
		$modelInstance->$method($collection,$filename);
		
	}
	
	public function backgroundImportAction(){
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		
		$model = $this->getRequest()->getParam('model');
		$filename = $this->getRequest()->getParam('collection');
		$method = $this->getRequest()->getParam('method');

		$modelInstance = Mage::getModel($model . '/standard');
		$modelInstance->$method($filename);
		
	}
	
	public function finallyAction(){
		$this->loadLayout();
		
		$block = $this->getLayout()->createBlock('migrator/finally');
		$this->getLayout()->getBlock('content')->append($block)->toHtml();
		
		$this->renderLayout();
	}
	
	public function getStandard(){
		$model = Mage::getModel('migrator/standard');
		return $model;
	}
	
}
