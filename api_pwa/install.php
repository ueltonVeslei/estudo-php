<?php
# inicializa o Magento
require_once '../app/Mage.php';
require_once 'app/Core/Install.php';

Mage::app()->setCurrentStore('admin');

$res = Install::init();

if($res)
    echo 'Banco criado com sucesso!';
else
    echo 'Não foi possível gerar banco';
