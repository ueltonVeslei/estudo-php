<?php
/**
 * Onestic - Smart PBMs
 *
 * @title      Magento -> Módulo Smart PBMs
 * @category   Integração
 * @package    Onestic_Smartpbm
 * @author     Onestic
 * @copyright  Copyright (c) 2016 Onestic
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_Smartpbm_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function getConfigData($path) {
	    return Mage::getStoreConfig('smartpbm/' . $path);
	}
	
	public function setConfigData($path,$value) {
	    $data = new Mage_Core_Model_Config();
	    $data->saveConfig('smartpbm/' . $path, $value, 'default', 0);
	}
	
}