<?php
$installer = Mage::getResourceModel('sales/setup', 'default_setup');

$installer->startSetup();

#$installer->addAttribute('order', 'skyhub_plp', array());

$installer->addAttribute('quote', 'interest', array
(
    'label' => 'Juros',
    'type'  => 'decimal',
));

$installer->addAttribute('quote', 'base_interest', array
(
    'label' => 'Base Juros',
    'type'  => 'decimal',
));

$installer->addAttribute('quote_address', 'interest', array
(
    'label' => 'Juros',
    'type'  => 'decimal',
));

$installer->addAttribute('quote_address', 'base_interest', array
(
    'label' => 'Base Juros',
    'type'  => 'decimal',
));

$installer->addAttribute('order', 'base_interest', array
(
    'label' => 'Base Juros',
    'type'  => 'decimal',
));


$installer->addAttribute('order', 'interest', array
(
    'label' => 'Juros',
    'type'  => 'decimal',
));

$installer->addAttribute('invoice', 'base_interest', array
(
    'label' => 'Base Juros',
    'type'  => 'decimal',
));

$installer->addAttribute('invoice', 'interest', array
(
    'label' => 'Juros',
    'type'  => 'decimal',
));

$installer->addAttribute('creditmemo', 'base_interest', array
(
    'label' => 'Base Juros',
    'type'  => 'decimal',
));

$installer->addAttribute('creditmemo', 'interest', array
(
    'label' => 'Juros',
    'type'  => 'decimal',
));

$installer->endSetup();