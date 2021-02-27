<?php

class Briel_ReviewPlus_Model_Observer {
	
	// Observer model class version 1.0.0
	public function logClients($observer) {
		// set timezone
		date_default_timezone_set(Mage::getStoreConfig('general/locale/timezone', Mage::app()->getStore()));
		// retrieve ORDER ID and STATUS from event dispatch
		$order_id = $observer->getEvent()->getOrder()->getId();
		$order_status = $observer->getEvent()->getOrder()->getStatus();
		// IF ReviewPlus is enabled, start logging
		$reviewplus_enable = Mage::getStoreConfig('reviewplus_options/reviewplus_config/enable_disable', Mage::app()->getStore());
		if ($reviewplus_enable == 1) {
			// IF order_status is Complete, log user, else skip
			$config_statuses = Mage::getStoreConfig('reviewplus_options/reviewplus_config/select_status', Mage::app()->getStore());
			$config_statuses_arr = explode(',', $config_statuses);
			if (in_array($order_status, $config_statuses_arr)) {
				// get order data
				$order = Mage::getModel('sales/order')->load($order_id);
				$increment_id = $order->getIncrementId();
				$customer_name = $order->getCustomerName();
				if ($customer_name == 'Guest') {
					$billing_address_id = $order->getBillingAddressId();
					$order_address = Mage::getModel('sales/order_address')->load($billing_address_id);
					$customer_name = $order_address->getName();
				}
				$customer_email = $order->getCustomerEmail();
				$ordered_products_collection = $order->getAllItems();
				$ordered_products = array();
				foreach($ordered_products_collection as $ordered_prod) {
					$tmp_sku = $ordered_prod->getSku();
					$_prod = Mage::getModel('catalog/product')->loadByAttribute('sku', array('eq' => $sku));
					if ($_prod->getTypeId() == "simple") {
						$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($_prod->getId());
						if (!$parentIds) {
							$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($_prod->getId());
						}
						if (isset($parentIds[0])) {
							$parent = Mage::getModel('catalog/product')->load($parentIds[0]);
							$parent_sku = $parent->getSku();
							if (!in_array($parent_sku, $ordered_products)) {
								$ordered_products[] = $parent_sku;
							}
						} else {
							if (!in_array($sku, $ordered_products)) {
								$ordered_products[] = $sku;
							}
						}
					} // end of product TYPE IF
				}
				$ordered_products = implode(", ", $ordered_products);
				// if collection is empty LOG new data
				$clientlog_collection = Mage::getModel('reviewplus/clientlog')->getCollection();
				$clientlog_collection->addFieldToFilter('order_id', $order_id);
				if (count($clientlog_collection) == 0) {
					// instance new model and write user data
					$clientlog_db = Mage::getModel('reviewplus/clientlog');
					$clientlog_db->setData('enable', 1)->save();
					$clientlog_db->setData('order_id', $order_id)->save();
					$clientlog_db->setData('customer_name', $customer_name)->save();
					$clientlog_db->setData('customer_email', $customer_email)->save();
					$clientlog_db->setData('ordered_products', $ordered_products)->save();
					$clientlog_db->setData('status', 0)->save();
					$clientlog_db->setData('time_sent', 0)->save();
					// calculate send time and save timestamp
					$days_delay_config = Mage::getStoreConfig('reviewplus_options/reviewplus_config/days_delay', Mage::app()->getStore());
					$days_delay = (24 * 60 * 60) * (int)$days_delay_config;
					$timestamp = time() + $days_delay;
					$clientlog_db->setData('due_date', $timestamp)->save();
				} // end collection count IF
			} // end of STATUS IF
		} // end of IF ENABLED
	} // end of method

	
	public function sendFollowupEmail() {
		// set timezone
		date_default_timezone_set(Mage::getStoreConfig('general/locale/timezone', Mage::app()->getStore()));
		// get a collection of contacts based on due date: today
		$current_time = mktime(23, 59, 59);
		$emails_per_cron = Mage::getStoreConfig('reviewplus_options/reviewplus_config/mails_per_cron', Mage::app()->getStore());
		$clientlog_collection = Mage::getModel('reviewplus/clientlog')->getCollection();
		$clientlog_collection->addFieldToFilter('status', 0)
				   			 ->addFieldToFilter('send_time', array('lteq' => $current_time))
				   			 ->getSelect()
							 ->limit((int)$emails_per_cron);

		if (count($clientlog_collection) == 0) {
			// do nothing, means there are no due clients
		} else {
			foreach ($clientlog_collection as $val) {
				$clientlog_db = Mage::getModel('reviewplus/clientlog')->load($val->getId());
				$send_time = $clientlog_db->getData('send_time');
				$order_id = $clientlog_db->getData('order_id');
				$customer_name = $clientlog_db->getData('customer_name');
				$customer_email = $clientlog_db->getData('customer_email');
				$ordered_products = $clientlog_db->getData('ordered_products');
				$status = $clientlog_db->getData('status');
				// use HOUR from config for when to start sending mails
				$cron_hour = Mage::getStoreConfig('reviewplus_options/reviewplus_config/cron_hour', Mage::app()->getStore());
				if (date('G', time()) >= $cron_hour) {
					// get current ORDER status
					$sales_order_db = Mage::getModel('sales/order')->load($order_id);
					// $order_status = $sales_order_db->getStatus();
					$order_increment_id = $sales_order_db->getIncrementId();

					$salt = "Magento extension created by Briel Software";
					$hash = md5($customer_name.$order_id.$salt);
					$review_page_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'reviewplus/index/index'.'/?oid='.$order_id.'&hash='.$hash;
					// get sender data from Store Sales Rep.
					$sender_name = Mage::getStoreConfig('trans_email/ident_sales/name');
					$sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
					// get template ID
					$email_template_id = Mage::getStoreConfig('reviewplus_options/reviewplus_config/email_template', Mage::app()->getStore()->getId());
					$email_template_vars = array();
					$email_template_vars['namevar'] = $customer_name;
					$email_template_vars['review_page_url'] = $review_page_url;
					$email_template_vars['order_increment_id'] = $order_increment_id;
					// ordered product(s) name
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
					// send Bcc if config is set
					$config_bcc = Mage::getStoreConfig('reviewplus_options/reviewplus_config/bcc_email', Mage::app()->getStore()->getId());
					if (isset($config_bcc)) {
						$mail->addBcc($config_bcc);
					}
					$mail->sendTransactional($email_template_id, array('name' => $sender_name, 'email' => $sender_email), $customer_email, $customer_name, $email_template_vars, $store_id);
				} // end of HOUR IF
			} // end of COLLECTION FOREACH
		} // end of COLLECTION COUNT ELSE
	} // end of sendFollowupEmail() method

} // end of class
?>