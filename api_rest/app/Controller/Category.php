<?php
class Controller_Category extends Controller {

	//Obtem categorias e subcategorias do produto
	protected function _get() {

		//Instacia model category
		$category = new Model_Category();
		//Variavel de retorno das categorias
		$categories = array();

		$body = $this->getData('body');
		if($body->productId >= 0){
			//Obtem as categorias
			$categories = $category->_getCategories($body->productId);
		}

		$this->setResponse('status', Standard::STATUS200);
		return $this->setResponse('data',$categories);
	}
	

}