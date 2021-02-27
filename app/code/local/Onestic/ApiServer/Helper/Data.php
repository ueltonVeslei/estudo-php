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
 * @package    Onestic_ApiServer
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Onestic_ApiServer_Helper_Data extends Mage_Core_Helper_Abstract {
	
    protected function getConfig($field) {
        return Mage::getStoreConfig('apiserver/geral/' . $field, Mage::app()->getStore());
    }
    
    public function getUf($cep) {
        $endereco = @file_get_contents('http://webservice.kinghost.net/web_cep.php?auth=24fc1da7de21ab0cddf57fdd07757cee&formato=json&cep='.$cep);
        $endereco = json_decode($endereco);
        echo $endereco->uf;
    }
    
    public function getCnpjEmissorNf() {
        return $this->getConfig('cnpj');
    }
    
    public function getPdv() {
        return $this->getConfig('pdv');
    }
    
    public function log($msg) {
    	Mage::log($msg,0,'onestic_apiserver.log');
    }
    
    
}