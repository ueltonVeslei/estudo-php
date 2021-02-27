<?php
/**
 * Add the auto_add_product_ids attrbute to the database
 * 
 * The auto_add_product_ids attribute is an attribute part of every product that
 * can contain a comma separated list of product ids to be added to the cart automatically when 
 * when the product is added to the cart
 * 
 * @copyright 2009 Iceberg Commerce
 */


$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$installer->addAttribute('catalog_product', 'auto_add_product_ids', array(
        'backend'       => '',
        'frontend'      => '',
        'label'         => 'Auto Add Product Ids (comma separated list)',
        'input'         => 'text',
        'type'          => 'varchar',
        'class'         => '',
        'source'        => '',
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'       => true,
        'required'      => false,
        'user_defined'  => true,
        'default'       => '',
        'searchable'    => false,
        'filterable'    => false,
        'unique'        => false,
        'comparable'    => false,
        'visible_on_front'  => false,
));

$installer->endSetup();