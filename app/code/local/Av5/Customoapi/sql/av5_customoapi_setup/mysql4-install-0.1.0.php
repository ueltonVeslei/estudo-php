<?php
$installer = $this;

$installer->startSetup();

$installer->addAttribute('order', 'marketplace_id', array());
$installer->addAttribute('order', 'marketplace', array());

$installer->endSetup();