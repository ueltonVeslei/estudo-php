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
 * @package    Onestic_Vidalink
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Onestic_Vidalink_Helper_Data extends Mage_Core_Helper_Abstract {
	
    protected function getConfig($field) {
        return Mage::getStoreConfig('vidalink/geral/' . $field, Mage::app()->getStore());
    }
    
    public function getUf($cep) {
        //$endereco = @file_get_contents('https://webservice.kinghost.net/web_cep.php?auth=24fc1da7de21ab0cddf57fdd07757cee&formato=json&cep='.$cep);
        //$endereco = json_decode($endereco);

        $uf_retorno = NULL;

        $estado = [
            ['AC','69900000','69999999'],
            ['AL','57000000','57999999'],
            ['AM','69000000','69299999'],
            ['AM','69400000','69899999'],
            ['AP','68900000','68999999'],
            ['BA','40000000','48999999'],
            ['CE','60000000','63999999'],
            ['DF','70000000','72799999'],
            ['DF','73000000','73699999'],
            ['ES','29000000','29999999'],
            ['GO','72800000','72999999'],
            ['GO','73700000','76799999'],
            ['MA','65000000','65999999'],
            ['MG','30000000','39999999'],
            ['MS','79000000','79999999'],
            ['MT','78000000','78899999'],
            ['PA','66000000','68899999'],
            ['PB','58000000','58999999'],
            ['PE','50000000','56999999'],
            ['PI','64000000','64999999'],
            ['PR','80000000','87999999'],
            ['RJ','20000000','28999999'],
            ['RN','59000000','59999999'],
            ['RO','76800000','76999999'],
            ['RR','69300000','69399999'],
            ['RS','90000000','99999999'],
            ['SC','88000000','89999999'],
            ['SE','49000000','49999999'],
            ['SP','01000000','19999999'],
            ['TO','77000000','77999999']
        ];

        foreach($estado as $uf){
            if (($cep >= $uf[1]) && ($cep <= $uf[2])){
                $uf_retorno = $uf[0];
            }
        }

        return $uf_retorno;
    }
    
    public function getCnpjEmissorNf() {
        return $this->getConfig('cnpj');
    }
    
    public function getPdv() {
        return $this->getConfig('pdv');
    }
    
    public function log($msg) {
    	Mage::log($msg,0,'onestic_vidaling.log');
    }
    
    
}