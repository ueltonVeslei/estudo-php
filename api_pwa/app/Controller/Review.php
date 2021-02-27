<?php
class Controller_Review extends Controller {

	// Retorna os reviews de um produto
	protected function _get() {
		if ($productId = $this->getData('ID')) {
			$reviews = Mage::getModel('review/review')->getCollection()
			    ->addStoreFilter(Mage::app()->getStore()->getId())
			    ->addEntityFilter('product', $productId)
			    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
			    ->setDateOrder()
	    	->addRateVotes();

			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data',$reviews->toArray());
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

	// Inclui review em um produto
	protected function _post() {
		if ($post = (array)$this->getData('body')) {
			$customer = Mage::getModel('customer/customer')->load($post['customer_id']);

			$_review = Mage::getModel('review/review')
				->setEntityPkValue($post['product_id'])
				->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
				->setEntityId(1)
				->setTitle($post['title'])
				->setDetail($post['detail'])
				->setStoreId(Mage::app()->getStore()->getId())
				->setStores(array(Mage::app()->getStore()->getId()))
				->setCustomerId($customer->getId())
				->setNickname($customer->getFirstname())
			->save();

			$reviews = Mage::getModel('review/review')->getCollection()
			    ->addStoreFilter(Mage::app()->getStore()->getId())
			    ->addEntityFilter('product', $post['product_id'])
			    ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
			    ->setDateOrder()
	    	->addRateVotes();

			$this->setResponse('status',Standard::STATUS200);
			$this->setResponse('data',$reviews->toArray());

		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

}