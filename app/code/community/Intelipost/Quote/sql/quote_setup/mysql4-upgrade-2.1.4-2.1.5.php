<?php

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();
$installer->updateAttribute('catalog_product', 'frete_price', 'is_visible_on_front', 0);
$installer->updateAttribute('catalog_product', 'prazo_produto', 'is_visible_on_front', 0);
$installer->updateAttribute('catalog_product', 'intelipost_sub_fora_fg', 'is_visible_on_front', 0);

$installer->endSetup();
