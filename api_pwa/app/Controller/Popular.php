<?php
class Controller_Popular extends Controller {

	// Retornar os dados dos pedidos
	protected function _post() {
		if ($post = (array)$this->getData('body')) {
			$results = null;

			if ($post['type'] == 'products') {
				$results = Mage::getModel('catalogsearch/query')
					->getCollection()
			     	->setPopularQueryFilter()
			    	->setPageSize($post['qty'])
			    ->toArray();
			}

			if ($post['type'] == 'categories') {
				//TODO: fazer para pegar categorias mais populares
				$results = Mage::getModel('catalog/category')
					->getCollection()
				    ->addAttributeToSelect('*')
				    ->addAttributeToFilter('level', 2)
				    ->addAttributeToFilter('is_active', 1)
				->toArray();
			}

			$this->setResponse('status',Standard::STATUS200);
			return $this->setResponse('data',$results);
		}

		$this->setResponse('status',Standard::STATUS404);
		$this->setResponse('data','Dados não informados');
	}

	// Inclui comentário no pedido
	protected function _get() {}

	// Excluir comentário do pedido
	protected function _delete() {}

}