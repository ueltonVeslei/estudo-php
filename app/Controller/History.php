<?php
class Controller_History extends Controller {

	// Retornar os dados dos pedidos
	protected function _get() {
		if($customerID = $this->getData('ID')) {
			$customer = Mage::getModel('customer/customer')->load($customerID);

	        if ($customer->getId()) {
	            try {
					$orders = Mage::getModel('sales/order')
						->getCollection()
						->addAttributeToSort('increment_id','desc')
					->addFieldToFilter('customer_id', $customerID);

	                $this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data',$orders->toArray());
	            } catch (Exception $exception) {
	                $this->setResponse('status',Standard::STATUS500);
					$this->setResponse('data','Ocorreu um erro: ' . $e->getMessage());
	            }
	        }
		} else {
			$this->setResponse('status',Standard::STATUS404);
			$this->setResponse('data','Dados não informados');
		}
	}

	// Inclui comentário no pedido
	protected function _post() {}

	// Excluir comentário do pedido
	protected function _delete() {}

}