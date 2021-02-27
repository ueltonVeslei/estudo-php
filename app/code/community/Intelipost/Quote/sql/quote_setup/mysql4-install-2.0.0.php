<?php

$installer = $this;
$installer->startSetup();

$sql = <<< SQLTEXT
create table intelipost_quote
(
    id int not null auto_increment,
    primary key(id)
);
SQLTEXT;

//$installer->run($sql);

//demo
//Mage::getModel('core/url_rewrite')->setId(null);
//demo

$installer->endSetup();

