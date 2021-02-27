<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Order (Pedido)
 * @package    Av5_Avisopagamento
 * @copyright  Copyright (c) 2015 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Av5_Avisopagamento_Model_Updater
 *
 * @category   Order
 * @package    Av5_Avisopagamento
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */

class Av5_Avisopagamento_Model_Updater extends Varien_Object {
	
	public function update() {
		if (!$this->_canExecute())
			return false;
		
		$statusFilter = Mage::getStoreConfig('av5avisopagamento/general/filter');
		$statusFilter = explode(',',$statusFilter);
		if (!is_array($statusFilter))
			$statusFilter = array($statusFilter);
		
		$paymentsFilter = Mage::getStoreConfig('av5avisopagamento/general/payments');
		$paymentsFilter = explode(',',$paymentsFilter);
		if (!is_array($paymentsFilter))
			$paymentsFilter = array($paymentsFilter);
		
		$days = Mage::getStoreConfig('av5avisopagamento/general/days');
		$limit = Mage::getStoreConfig('av5avisopagamento/general/limit');
		
		$to = date('Y-m-d H:i:s',strtotime('yesterday'));
		$from = date('Y-m-d H:i:s',strtotime($to . ' ' . $days . ' days ago'));

        $sendEmail = Mage::getStoreConfig('av5avisopagamento/general/send_email');

		$orders = Mage::getModel('sales/order')->getCollection();
		$orders->addFieldToFilter('status',array(array('in' => $statusFilter)))
			->addFieldToFilter('payment.method',array(array('in' => $paymentsFilter)))
			->addFieldToFilter('created_at',array('from'=>$from,'to'=>$to));
		$orders->getSelect()->join(
				array('payment' => 'sales_flat_order_payment'),
				'main_table.entity_id=payment.parent_id',
				array('payment_method' => 'payment.method')
			)
			->limit($limit);
		
		$logModel = Mage::getResourceModel('av5avisopagamento/log');
		$lastUpdated = $logModel->getOrders();
		if ($lastUpdated) {
			$orders->addFieldToFilter('entity_id',array(array('nin' => $lastUpdated)));
		}
		
		Mage::log('SQL: ' . $orders->getSelect()->assemble(),null,'av5_avisopagamento.log');
		
		foreach ($orders as $order) {
			$logModel->logOrder($order->getId());
			$created = strtotime($order->getCreatedAt());
			$until = strtotime($order->getCreatedAt() . " +".$days." days");
			$today = strtotime(date('Y-m-d H:i:s'));
			$daysLeft = ceil(($until - $today) / 86400);
			Mage::log('AVISO ENVIADO: ' . $order->getIncrementId() . ' - ' . $daysLeft . ' dias restantes',null,'av5_avisopagamento.log');
			if ($sendEmail) {
                $this->_sendEmail($order,$daysLeft);
            }
		}
		
		if ($orders->count() == 0) {
			$logModel->cleanDatabase();
			$this->_updateExecution();
		}

		$this->_autoCancel($from,$statusFilter,$paymentsFilter);
	}
	
	private function _sendEmail($order, $daysLeft)
	{
		$emailTemplate  = Mage::getModel('core/email_template');
		$emailTemplate->loadDefault('av5avisopagamento');
	
		$salesData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
		$salesData['name'] = Mage::getStoreConfig('trans_email/ident_general/name');
		 
		$emailTemplate->setSenderName($salesData['name']);
		$emailTemplate->setSenderEmail($salesData['email']);
		 
		$emailTemplateVariables['order'] = $order;
		$emailTemplateVariables['store'] = Mage::app()->getStore();
		$emailTemplateVariables['days_to_cancel'] = $daysLeft;
		 
		$paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())->setIsSecureMode(true);
		$paymentBlock->getMethod()->setStore($order->getStore()->getId());
		$emailTemplateVariables['payment_html'] = $paymentBlock->toHtml();
		
		/*$text = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
		Mage::log($text,null,'av5_emailtester.html');*/
	
		$emailTemplate->send($order->getCustomerEmail(), $order->getStoreName(), $emailTemplateVariables);
	}

	private function _autoCancel($from,$status,$payment) {
		$cancelOrders = Mage::getStoreConfig('av5avisopagamento/general/auto_cancel');
		if ($cancelOrders) {
			$orders = Mage::getModel('sales/order')->getCollection();
			$orders->addFieldToFilter('status',array(array('in' => $status)))
				->addFieldToFilter('payment.method',array(array('in' => $payment)))
				->addFieldToFilter('created_at',array('lt'=>$from));
			
			$orders->getSelect()->join(
					array('payment' => 'sales_flat_order_payment'),
					'main_table.entity_id=payment.parent_id',
					array('payment_method' => 'payment.method')
			);
			
			foreach ($orders as $order) {
				Mage::log('PEDIDO CANCELADO: ' . $order->getIncrementId(),null,'av5_avisopagamento.log');
				$order->cancel();
				$order->save();
			}
		}
	}
	
	private function _canExecute() {
        $isActive = Mage::getStoreConfig('av5avisopagamento/general/active');
        if (!$isActive) {
            return false;
        }

		$date = Mage::getStoreConfig('av5avisopagamento/general/last_execution');
		if ($date) {
			$last = new DateTime($date);
			$now = new DateTime(date("Y-m-d H:i:s"));
			$interval = date_diff($last, $now);
		
			if ($interval->days >= 1) {
				return true;
			}
		} else {
			return true;
		}
		
		return false;
	}
	
	private function _updateExecution() {
		$date = new Mage_Core_Model_Config();
		$date->saveConfig('av5avisopagamento/general/last_execution', date("Y-m-d H:i:s",time()), 'default', 0);
		$date->cleanCache();
	}
}