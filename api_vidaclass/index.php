<?php

ini_set('error_reporting', -1);
ini_set('display_errors', 'On');

define('BASEPATH', dirname(__FILE__));

# inicializa o Magento
require_once('../app/Mage.php');

Mage::app()->setCurrentStore('default');

# Carrega a aplicação
include_once 'app/App.php';
App::run();