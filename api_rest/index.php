<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', E_ALL);
ini_set('error_log',getcwd() . '/var/log/php_error.log');

define('BASEPATH', dirname(__FILE__));

# inicializa o Magento
require_once('../app/Mage.php');

Mage::app()->setCurrentStore('admin');

# Carrega a aplicação
include_once 'app/App.php';
App::run();
