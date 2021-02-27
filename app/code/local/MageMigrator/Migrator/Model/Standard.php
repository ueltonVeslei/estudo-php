<?php

class MageMigrator_Migrator_Model_Standard extends Mage_Core_Model_Abstract {
	
	/**
	 * @desc Lista todos os setores disponiveis para a migração
	 */
	public function getSetors(){
		
		$setors = array(
			array( 'value' => 'migstore', 'name' => 'Stores, Websites e Views'),		
 			array( 'value' => 'migcms', 'name' => 'CMS Pages'),		
			array( 'value' => 'migcategory', 'name' => 'Categorias'),
			array( 'value' => 'migproduct', 'name' => 'Produtos'),
			array( 'value' => 'migcatalogsearch', 'name' => 'Termos de pesquisa'),		
			array( 'value' => 'migurlrewrite', 'name' => 'Reescrita de Url'),		
			array( 'value' => 'migcustomer', 'name' => 'Clientes'),			
			array( 'value' => 'mignewsletter', 'name' => 'Clientes de Newsletter'),			
			array( 'value' => 'migrule', 'name' => 'Regras de carrinho e de catalogo'),			
		);
		
		return $setors;
		
	}
	
	/**
	 * @desc Verifica se o setor é valido
	 * @param string $setor
	 */
	public function isValidSetor( $setor ){
		$return = false;
		
		foreach($this->getSetors() as $code){
			if($code['value'] == $setor){
				$return = true;
			}
		}
		
		return $return;
	}
	
}