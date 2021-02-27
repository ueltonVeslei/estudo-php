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
 * @category   Integrator
 * @package    Onestic_Skyhub
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Onestic_Skyhub_Helper_Data extends Mage_Core_Helper_Abstract {
	
    public function getConfig($field) {
        return Mage::getStoreConfig('onestic_skyhub/geral/' . $field, Mage::app()->getStore());
    }
    
    public function getEmail() {
        return $this->getConfig('email');
    }
    
    public function getToken() {
        return $this->getConfig('token');
    }
    
    public function getAccountManager() {
    	return $this->getConfig('accountmanager');
    }
    
    public function getLimit() {
        return $this->getConfig('limit');
    }
    
    public function updateConfig($field, $value) {
        $config = new Mage_Core_Model_Config();
        $config->saveConfig('onestic_skyhub/geral/' . $field, $value, 'default', 0);
        $config->cleanCache();
    }
    
}