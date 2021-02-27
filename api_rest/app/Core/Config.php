<?php
class Config {
	CONST BASEURL 		= 'https://admin.farmadelivery.com.br/api_rest/';
	CONST BASEURI 		= '/api_rest/';
	CONST USEREMAIL 	= 'apidsc@onestic.com.br';
	CONST TOKEN 		= '8FjTHZGp72ggDQH5LcT7aFZt377XKmBn8QQf6mRG';
	CONST HEADER_TOKEN  = 'YXBpZHNjQG9uZXN0aWMuY29tLmJyOjhGalRIWkdwNzJnZ0RRSDVMY1Q3YUZadDM3N1hLbUJuOFFRZjZtUkc';
	CONST JWTSECRET 		= '1a2b3c4d5e--jwt';
	// DATABASE CONFIGS
	//CONST DB_HOST		= '127.0.0.1';
	//CONST DB_USER		= 'root';
	//CONST DB_PASS		= 'rp7evh5150';
	//CONST DB_NAME		= 'rest_db';
	// MAGENTO CONFIGS
	CONST STORE 		= 1;
	CONST WEBSITE 		= 1;

	static $allowed_domains = [
	    'farmadelivery.com.br',
        'admin.farmadelivery.com.br',
        'checkout.farmadelivery.com.br',
        'portoseguro.farmadelivery.com.br',
        'credicard.farmadelivery.com.br',
        'hipercard.farmadelivery.com.br',
        'santander.farmadelivery.com.br',
        'itaucard.farmadelivery.com.br',
        'bomparatodos.net',
        'vcdelivery.com.br'
    ];
}