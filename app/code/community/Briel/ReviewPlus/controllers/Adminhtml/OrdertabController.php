<?php

class Briel_ReviewPlus_Adminhtml_OrdertabController extends Mage_Adminhtml_Controller_Action {

	public function lognowAction() {
		$post = $this->getRequest()->getPost();
		try {
			$clientlog_db = Mage::getModel('reviewplus/clientlog');
			$clientlog_db->setData('enable', 1)->save();
			$clientlog_db->setData('order_id', $post['reviewplus-lognow']['order_id'])->save();
			$clientlog_db->setData('customer_name', $post['reviewplus-lognow']['customer_name'])->save();
			$clientlog_db->setData('customer_email', $post['reviewplus-lognow']['customer_email'])->save();
			$clientlog_db->setData('ordered_products', $post['reviewplus-lognow']['ordered_products'])->save();
			$clientlog_db->setData('due_date', time())->save();
			$clientlog_db->setData('status', 0)->save();
			$clientlog_db->setData('time_sent', 0)->save();
			// success message
			$success_msg = $this->__('Order successfully logged. The client will receive a product review followup email as per extension settings.');
			Mage::getSingleton('adminhtml/session')->addSuccess($success_msg);
		} catch (Exception $ex) {
			$session = Mage::getSingleton('core/session');
			$session->addError($this->__('Unable to log order.'));
		}	
		$this->_redirect('adminhtml/sales_order/view' , array('order_id' => $post['reviewplus-lognow']['order_id']));
	}

}
?>