<?php

/**
 * @category   RMO
 * @package    RMO_Integrator
 * @author     Renato Marcelino <renato@skyhub.com.br>
 * @company    SkyHub
 * @copyright (c) 2013, SkyHub
 * 
 * 
 * SkyHub: Especialista em integrações para e-commerce.
 * Integramos sua loja Magento com os principais Marketplaces
 * e ERPs do mercado nacional. 
 * Para mais informações acesse: www.skyhub.com.br
 */


$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE `{$installer->getTable('rmointegrator/catalog_product_integrator')}` (
      `integrator_product_id` int(11) NOT NULL auto_increment,
      `product_id` int(11),
      `product_sku` text NOT NULL,
      `status` int(11) NOT NULL,
      PRIMARY KEY  (`integrator_product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;  ");
$installer->endSetup();