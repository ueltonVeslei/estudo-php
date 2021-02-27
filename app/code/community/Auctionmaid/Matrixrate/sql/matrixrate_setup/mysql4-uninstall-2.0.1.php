<?php
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('shipping_matrixrate')};
DELETE FROM {$this->getTable('core/config_data')} WHERE path like 'carriers/freeoptionalshipping/%';
DELETE FROM {$this->getTable('core/config_data')} WHERE path like 'carriers/matrixrate/%';
DELETE FROM {$this->getTable('eav_attribute')} WHERE attribute_code =  'exclude_free_shipping';
");

$installer->endSetup();
