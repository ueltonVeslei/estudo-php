<?php

$installer = $this;
$installer->startSetup();

$installer->run("
		
CREATE TABLE IF NOT EXISTS `catalog_product_ean` (
  `ean_id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ean` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `principio_ativo` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `fabricante` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `abc` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `dcb` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`ean_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

");

$installer->endSetup();