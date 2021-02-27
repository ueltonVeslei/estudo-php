<?php
echo '===== INICIA EXPORT ELASTIC =====' . PHP_EOL;
define('ABSOLUTE_PATH', dirname(__FILE__));
require ABSOLUTE_PATH . '/vendor/autoload.php';
require ABSOLUTE_PATH . '/ElasticIndex.php';
require_once(ABSOLUTE_PATH . '/../app/Mage.php');
require ABSOLUTE_PATH . '/Exporter.php';


$exporter = new Onestic_Elastic_Exporter();

Mage::app()->setCurrentStore('default');

$exporter->export();
echo '===== FIM EXPORT ELASTIC =====' . PHP_EOL;