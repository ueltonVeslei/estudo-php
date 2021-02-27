<?php
require_once ("app/Mage.php");
ini_set('display_errors', 1);
umask(0);
Mage::app();

//$limit = 100;
//$curPage = 1;
$collection = Mage::getModel('customer/address')->getCollection();
//$collection->setPageSize($limit);
//$collection->setCurPage($curPage);

foreach($collection as $address) {
    $customer = Mage::getModel('customer/address')->load($address->getId());

    // Atualiza CEP para estado de Acre
    if ($customer->getPostcode() >= 69900000 && $customer->getPostcode() <= 69999999){
        $customer->setRegionId(182);
    }

    // Atualiza CEP para estado de Alagoas
    if ($customer->getPostcode() >= 69900000 && $customer->getPostcode() <= 69999999){
        $customer->setRegionId(183);
    }

    // Atualiza CEP para estado de Amapá
    if ($customer->getPostcode() >= 68900000 && $customer->getPostcode() <= 68999999){
        $customer->setRegionId(184);
    }

    // Atualiza CEP para estado de Amazonas
    if ($customer->getPostcode() >= 69000000 && $customer->getPostcode() <= 69299999 || $customer->getPostcode() >= 69400000 && $customer->getPostcode() <= 69899999){
        $customer->setRegionId(185);
    }

    // Atualiza CEP para estado de Bahia
    if ($customer->getPostcode() >= 40000000 && $customer->getPostcode() <= 48999999){
        $customer->setRegionId(186);
    }

    // Atualiza CEP para estado de Ceará
    if ($customer->getPostcode() >= 60000000 && $customer->getPostcode() <= 63999999){
        $customer->setRegionId(187);
    }

    // Atualiza CEP para estado de Distrito Federal
    if ($customer->getPostcode() >= 70000000 && $customer->getPostcode() <= 72799999 || $customer->getPostcode() >= 73000000 && $customer->getPostcode() <= 73699999){
        $customer->setRegionId(208);
    }

    // Atualiza CEP para estado de Espírito Santo
    if ($customer->getPostcode() >= 29000000 && $customer->getPostcode() <= 29999999){
        $customer->setRegionId(188);
    }

    // Atualiza CEP para estado de Goiás
    if ($customer->getPostcode() >= 72800000 && $customer->getPostcode() <= 72999999 || $customer->getPostcode() >= 73700000 && $customer->getPostcode() <= 76799999){
        $customer->setRegionId(189);
    }

    // Atualiza CEP para estado de Maranhão
    if ($customer->getPostcode() >= 65000000 && $customer->getPostcode() <= 65999999){
        $customer->setRegionId(190);
    }

    // Atualiza CEP para estado de Mato Grosso
    if ($customer->getPostcode() >= 78000000 && $customer->getPostcode() <= 78899999){
        $customer->setRegionId(191);
    }

    // Atualiza CEP para estado de Mato Grosso do Sul
    if ($customer->getPostcode() >= 79000000 && $customer->getPostcode() <= 79999999){
        $customer->setRegionId(192);
    }

    // Atualiza CEP para estado de Minas Gerais
    if ($customer->getPostcode() >= 30000000 && $customer->getPostcode() <= 39999999){
        $customer->setRegionId(193);
    }

    // Atualiza CEP para estado de Pará
    if ($customer->getPostcode() >= 66000000 && $customer->getPostcode() <= 68899999){
        $customer->setRegionId(194);
    }

    // Atualiza CEP para estado de Paraíba
    if ($customer->getPostcode() >= 58000000 && $customer->getPostcode() <= 58999999){
        $customer->setRegionId(195);
    }

    // Atualiza CEP para estado de Paraná
    if ($customer->getPostcode() >= 80000000 && $customer->getPostcode() <= 87999999){
        $customer->setRegionId(196);
    }

    // Atualiza CEP para estado de Pernambuco
    if ($customer->getPostcode() >= 50000000 && $customer->getPostcode() <= 56999999){
        $customer->setRegionId(197);
    }

    // Atualiza CEP para estado de Piauí
    if ($customer->getPostcode() >= 64000000 && $customer->getPostcode() <= 64999999){
        $customer->setRegionId(198);
    }

    // Atualiza CEP para estado de Rio de Janeiro
    if ($customer->getPostcode() >= 20000000 && $customer->getPostcode() <= 28999999){
        $customer->setRegionId(199);
    }

    // Atualiza CEP para estado de Rio Grande do Norte
    if ($customer->getPostcode() >= 59000000 && $customer->getPostcode() <= 59999999){
        $customer->setRegionId(200);
    }

    // Atualiza CEP para estado de Rio Grande do Sul
    if ($customer->getPostcode() >= 90000000 && $customer->getPostcode() <= 99999999){
        $customer->setRegionId(201);
    }

    // Atualiza CEP para estado de Rondônia
    if ($customer->getPostcode() >= 76800000 && $customer->getPostcode() <= 76999999){
        $customer->setRegionId(202);
    }

    // Atualiza CEP para estado de Roraima
    if ($customer->getPostcode() >= 69300000 && $customer->getPostcode() <= 69399999){
        $customer->setRegionId(203);
    }

    // Atualiza CEP para estado de Santa Catarina
    if ($customer->getPostcode() >= 88000000 && $customer->getPostcode() <= 89999999){
        $customer->setRegionId(204);
    }

    // Atualiza CEP para estado de São Paulo
    if ($customer->getPostcode() >= 0100000 && $customer->getPostcode() <= 19999999){
        $customer->setRegionId(205);
    }

    // Atualiza CEP para estado de Sergipe
    if ($customer->getPostcode() >= 49000000 && $customer->getPostcode() <= 49999999){
        $customer->setRegionId(206);
    }

    // Atualiza CEP para estado de Tocantins
    if ($customer->getPostcode() >= 77000000 && $customer->getPostcode() <= 77999999){
        $customer->setRegionId(207);
    }
    
    try {
        $customer->save();
        echo 'CEP ' .$customer->getPostcode(). ' Atualizado com sucesso ';
    }
    catch (Exception $ex) {
    }
}


