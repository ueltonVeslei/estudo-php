<?php

class MageMigrator_Migrator_Block_Form extends Mage_Core_Block_Template {
	
	public function getMessage(){
		
		$code = $this->getRequest()->getParam('code');
		$code = (int) $code;
		
		switch ($code) {
			case 1:
				return 'Por favor, selecione um setor.';
			break;
			
			default: 
				return false;
				break;
		}
	}
	
	public function getSetors(){
		$migrator = Mage::getModel('migrator/standard');
		return $migrator->getSetors();
	}
	
}