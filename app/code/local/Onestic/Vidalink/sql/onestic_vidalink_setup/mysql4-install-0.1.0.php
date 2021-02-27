<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('order', 'ov_autorizacao', array());
$installer->addAttribute('order', 'ov_cotacao', array());
$installer->addAttribute('order', 'ov_origem', array());
$installer->addAttribute('order', 'ov_referencia', array());
$installer->addAttribute('order', 'ov_notafiscal', array());

$installer->endSetup();