<?php
/**
 * @category RMO
 * @package RMO_Integrator
 * @author Renato Marcelino <renato@skyhub.com.br>
 * @company SkyHub
 * @copyright (c) 2013, SkyHub
 *
 *
 * SkyHub: Especialista em integrações para e-commerce.
 * Integramos sua loja Magento com os principais Marketplaces
 * e ERPs do mercado nacional.
 * Para mais informações acesse: www.skyhub.com.br
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$tableName = $this->getTable('sales/order');

// Order
if(!$installer->getConnection()->tableColumnExists($tableName, 'skyhub_code')){
  $table_engine = $installer->getConnection()->raw_fetchRow("SHOW TABLE STATUS WHERE Name = '".$tableName."' ", 'Engine');
  if($table_engine == "InnoDB") {
    $installer->run("ALTER TABLE `{$tableName}` ADD `skyhub_code` varchar(255) DEFAULT NULL");
    $installer->run('ALTER TABLE ' . $tableName . ' ADD UNIQUE (skyhub_code);');
  }
}

$installer->endSetup();