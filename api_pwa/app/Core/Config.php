<?php
class Config {
	CONST BASEURI 		= '/api_pwa/';
	// MAGENTO CONFIGS
	CONST STORE 		= 1;
	CONST WEBSITE 		= 1;
	CONST EMAILSALE = 'farmadelivery@farmadelivery.com.br';
	CONST NAMESALE = 'Farmadelivery';

	static $allowed_domains = [
		'farmadelivery.com.br',
        'admin.farmadelivery.com.br',
        'checkout.farmadelivery.com.br',
		'portoseguro.farmadelivery.com.br',
		'japan.farmadelivery.com.br',
        'credicard.farmadelivery.com.br',
        'hipercard.farmadelivery.com.br',
        'santander.farmadelivery.com.br',
		'itaucard.farmadelivery.com.br',
		'iupp.farmadelivery.com.br',
		'poupafarma.farmadelivery.com.br',
		'vcdelivery.com.br',
		'poupafarma.com.br'
	];
	//Dados adicionados api atual
	CONST QTDDAYSPIRETOKEN = 18250;// Expire token
	CONST KEY              = 'as$%15%mN*+h#56$%';
	CONST IV               = '$HAIS564&*#$%i#$%';
	CONST TYPES_USER        = array(1 => 'cliente', 0 => 'visitante');
	CONST METHODS_CONTROLLERS_ACESS = array(
		//Visitante posição 0
		array(
				'Controller_Cart' => array('_get', '_post', '_put', '_delete', '_options'),
				'Controller_Checkout' => array('_get', '_post', '_delete', '_options'),
				'Controller_Coupon' => array('_get', '_post', '_put', '_delete', '_options'),
				'Controller_Crossover' => array('_get', '_post', '_put', '_delete', '_options'),
				'Controller_Customer' => array('_put'),
				'Controller_RecoveryPassword' => array('_post', '_put'),
				'Controller_Erro' => array('_get', '_post', '_put', '_delete', '_options'),
				'Controller_Frete' => array('_get', '_post', '_put', '_delete', '_options'),
				'Controller_Login' => array('_get', '_post', '_put', '_delete', '_options'),
				'Controller_Popular' => array('_get', '_post', '_put', '_delete', '_options'),
				'Controller_Preflight' => array('_get', '_post', '_put', '_delete', '_options'),
				'Controller_Product' => array('_get', '_post', '_put', '_delete', '_options'),
				'Controller_Review' => array('_get', '_put', '_delete', '_options'),
				'Controller_Shipping' => array('_get', '_post', '_put', '_delete', '_options')),
		//Cliente posição 1
		array('FULL')
	);
	public static function getBaseURL(){
		$res = self::getDomain() . self::BASEURI;
		return $res;
	}

	public static function getDomain(){
		$res = Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB);
		return $res;
	}
}
