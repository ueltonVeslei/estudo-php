<?php

class Briel_ReviewPlus_Adminhtml_ClientlogController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {
		$this->loadLayout();
		$this->_setActiveMenu('reviewplus_menu');
		$this->renderLayout();
	}

	public function massdeleteAction() {
		$post = $this->getRequest()->getPost();
		$clientlog_ids = $post['clientlog_id'];
		if (!is_array($clientlog_ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('reviewplus')->__('Please select at least one entry.'));
		} else {
			try {
				foreach($clientlog_ids as $id) {
					Mage::getModel('reviewplus/clientlog')->load($id)->delete();
				}
				$success_msg = $this->__('Entries deleted successfully.');
	            Mage::getSingleton('adminhtml/session')->addSuccess($success_msg);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function masssendAction() {
		$post = $this->getRequest()->getPost();
		$clientlog_ids = $post['clientlog_id'];
		if (!is_array($clientlog_ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('reviewplus')->__('Please select at least one entry.'));
		} else {
			try {
				foreach($clientlog_ids as $id) {
					$clientlog_db = Mage::getModel('reviewplus/clientlog')->load($id);
					$order_id = $clientlog_db->getData('order_id');
					$customer_name = $clientlog_db->getData('customer_name');
					$customer_email = $clientlog_db->getData('customer_email');
					$status = $clientlog_db->getData('status');
					$order_increment_id = Mage::getModel('sales/order')->load($order_id)->getIncrementId();
					if ($status == 0) {
						// create hash and url for email template
						$salt = "Magento extension created by Briel Software";
						$hash = md5($customer_name.$order_id.$salt);
						$review_page_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'reviewplus/index/index'.'/?oid='.$order_id.'&hash='.$hash;
						// get sender data from Store Sales Rep.
						$sender_name = Mage::getStoreConfig('trans_email/ident_sales/name');
					    $sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
						// get template ID and complete email variables
						$email_template_id = Mage::getStoreConfig('reviewplus_options/reviewplus_config/email_template', Mage::app()->getStore()->getId());
						$email_template_vars = array();
						$email_template_vars['namevar'] = $customer_name;
						$email_template_vars['review_page_url'] = $review_page_url;
						$email_template_vars['order_increment_id'] = $order_increment_id;
						// get ordered products and send to email as variable
						$ordered_products = $clientlog_db->getData('ordered_products');
						$ordered_products_arr = explode(", ", $ordered_products);
						$purchased_products = '';
						foreach($ordered_products_arr as $purchased_prod_sku) {
							$prod_loaded = Mage::getModel('catalog/product')->loadByAttribute('sku', trim($purchased_prod_sku));
							$purchased_prod_name = $prod_loaded->getName();
							$purchased_products .= "<span>".$purchased_prod_name."</span><br />";
						}
						$email_template_vars['purchased_products'] = $purchased_products;
						// get Store ID
						$store_id = Mage::app()->getStore()->getId();
						// set status as SENT on followup entry
						$clientlog_db->setData('status', 1)->save();
						$clientlog_db->setData('time_sent', time())->save();
						// send transactional, will not send if template has no subject
						$mail = Mage::getModel('core/email_template');
						$config_bcc = Mage::getStoreConfig('reviewplus_options/reviewplus_config/bcc_email', Mage::app()->getStore()->getId());
						$config_bcc_exploded = explode(',', $config_bcc);
						$mail->addBcc($config_bcc_exploded);
						$mail->sendTransactional($email_template_id, array('name' => $sender_name, 'email' => $sender_email), $customer_email, $customer_name, $email_template_vars, $store_id);
						$success_msg = $this->__('Followup emails has been sent for entry #'.$id);
						Mage::getSingleton('adminhtml/session')->addSuccess($success_msg);
					} else {
						$error_msg = $this->__('Entry #'.$id." already sent.");
						Mage::getSingleton('adminhtml/session')->addError($error_msg);
					}
				}
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			$this->_redirect('*/*/index');
		}
	}
}
?>