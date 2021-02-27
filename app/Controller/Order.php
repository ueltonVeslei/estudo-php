<?php
class Controller_Order extends Controller {

	// Retorna dados de um pedido
	protected function _get() {
		if($orderID = $this->getData('ID')) {
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderID);

	        if ($order->getId()) {
	            $this->setResponse('status',Standard::STATUS200);

	            $items = array();
			      foreach ($order->getAllItems() as $item) {
			        $items[] = array(
			            'id'            => $order->getIncrementId(),
			            'name'          => $item->getName(),
			            'price'         => $item->getPrice(),
			            'qty'   => $item->getQtyOrdered()
			        );
			    }
				return $this->setResponse('data',[
					'items' => $items,
					'shipping' => $order->getShippingAddress()->getFormated(true),
					'billing' => $order->getBillingAddress()->getFormated(true)
				]);
	        }
		}

		$this->setResponse('status',Standard::STATUS404);
		$this->setResponse('data','Dados não informados');
	}

	// Inclui comentário no pedido
	protected function _post() {}

	// Excluir comentário do pedido
	protected function _delete() {}

}