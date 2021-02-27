<?php
class Config {
	CONST BASEURL 		= 'https://admin.farmadelivery.com.br/api_vidaclass/';
	CONST BASEURI 		= '/api_vidaclass/';
	CONST USEREMAIL 	= 'apidsc@onestic.com.br';
	CONST TOKEN 		= '2R7zfJYfTu2uX35h7g9tXWWR5tG5CA94R7syGGTb';
	CONST HEADER_TOKEN  = 'YXBpZHNjQG9uZXN0aWMuY29tLmJyOjJSN3pmSllmVHUydVgzNWg3Zzl0WFdXUjV0RzVDQTk0UjdzeUdHVGI';

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
