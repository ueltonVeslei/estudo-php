<?php
/**
 * Onestic
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Integrator
 * @package    Onestic_ApiServer
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Onestic_Overcoupom_Helper_Data extends Mage_Core_Helper_Abstract {
	
    public function getConfig($field) {
        return Mage::getStoreConfig('overcoupom/geral/' . $field, Mage::app()->getStore());
    }
    
    
}