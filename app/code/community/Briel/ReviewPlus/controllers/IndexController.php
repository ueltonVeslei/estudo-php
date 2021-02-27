<?php

class Briel_ReviewPlus_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		$this->loadLayout();
		$this->renderLayout();
	}

	public function rateAction() {
		$post = $this->getRequest()->getPost();
		$skus = $post['sku'];
		try {
			// write details to REVIEWPLUS_REVIEWS table, create entry for each SKU
			foreach($skus as $sku) {
				$reviews_db = Mage::getModel('reviewplus/reviews')->load($post['rating-id'][$sku]);
				$reviews_db->setData('product_review_title', $post['title'][$sku])->save();
				$reviews_db->setData('product_review', $post['detail'][$sku])->save();
				$reviews_db->setData('product_rating', $post['rating'][$sku])->save();
				$reviews_db->setData('posted_time', time())->save();
			}
			// $session->addSuccess($this->__('Evaluarea dumneavoastra a fost inregistrata. Va multumim pentru timpul acordat!'));
		} catch(Exception $e) {
			// $session->addError($e->getMessage());
		}
		$this->_redirect('/');
	} // end of method
}
?>