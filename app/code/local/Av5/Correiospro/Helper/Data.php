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
 * @category   Shipping (Frete)
 * @package    Av5_Correiospro
 * @copyright  Copyright (c) 2013 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Av5_Correiospro_Helper_Data extends Mage_Core_Helper_Abstract {
	
	/**
	 * Formata um CEP informado
	 *
	 * @param string $zipcode
	 * @return boolean|Ambigous <string, mixed>
	 */
	public function _formatZip($zipcode) {
		$new = trim($zipcode);
		$new = preg_replace('/[^0-9\s]/', '', $new);
		 
		if ($this->getConfigData('validate_zipcode')) {
			$regex = "/^[0-9]{8}$/";
		} else {
			$regex = "/^[0-9]{7,8}$/";
		}
		
		if(!preg_match($regex, $new)){
			return false;
		} elseif(preg_match("/^[0-9]{7}$/", $new)){ // tratamento para CEP com 7 dígitos
			$new = "0" . $new;
		}
	
		return $new;
	}
	
	public function services() {
		$model = Mage::getModel("av5_correiospro/updater");
		return $model->toUpdate();
	}
	
	public function getServiceName($srv){
		$model = Mage::getModel("av5_correiospro/updater");
		return $model->getServiceName($srv);
	}
	
	public function allServices() {
		$model = Mage::getModel("av5_correiospro/updater");
		return $model->allServices();
	}
	
	public function hasToUpdate($srv) {
		$model = Mage::getModel("av5_correiospro/updater");
		return $model->hasToUpdate($srv);
	}
	
	public function updatedCount($srv) {
		$model = Mage::getModel("av5_correiospro/updater");
		return $model->updatedCount($srv);
	}
	
	public function allPromos() {
		$model = Mage::getModel("av5_correiospro/promotion");
		return $model->allPromos();
	}
	
	public function getConfigData($field)
	{
		$store = Mage::app()->getStore();
		$path = 'carriers/av5_correiospro/'.$field;
		return Mage::getStoreConfig($path, $store);
	}

	public function getSroUser() {
		$user = $this->getConfigData('login_sro');
		if (!$user) {
			$user = $this->getConfigData('webservices/tracking/user');
		}

		return $user;
	}
	
	public function getSroPass() {
		$pass = $this->getConfigData('password_sro');
		if (!$pass) {
			$pass = $this->getConfigData('webservices/tracking/pass');
		}

		return $pass;
	}

	public function log($msg) {
	    Mage::log($msg,null,'av5_correiospro.log');
	}
}