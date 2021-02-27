<?php
echo '===== INICIA EXPORT CATEGORIES ELASTIC =====' . PHP_EOL;
define('ABSOLUTE_PATH', dirname(__FILE__));
require ABSOLUTE_PATH . '/vendor/autoload.php';
require ABSOLUTE_PATH . '/ElasticIndex.php';
require_once(ABSOLUTE_PATH . '/../app/Mage.php');
require ABSOLUTE_PATH . '/ExporterCategories.php';

$exporter = new Onestic_Elastic_ExporterCategories();

Mage::app()->setCurrentStore('default');

$exporter->export();
echo '===== FIM EXPORT CATEGORIES ELASTIC =====' . PHP_EOL;