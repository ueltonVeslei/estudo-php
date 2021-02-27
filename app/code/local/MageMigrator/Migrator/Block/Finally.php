<?php

class MageMigrator_Migrator_Block_Finally extends Mage_Core_Block_Abstract {
	
	public function _toHtml(){
		$return  = '<h1>Mage Migrator</h1>';
		$return .= '<p class="success-msg">Operação conclu&iacute;da com sucesso</p>';
		return $return;
	}
	
}