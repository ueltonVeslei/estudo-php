<?php
Mage::helper('basic')->setVersionControlData(Mage::helper('basic')->getModuleName(), 'basic');

$intelipost_api = Mage::getModel('basic/intelipost_api');

$intelipost_api->apiRequest(Intelipost_Basic_Model_Intelipost_Api::GET, 'info', false, Mage::helper('basic')->getVersionControlModel());
$methods_info = $intelipost_api->apiResponseToObject();

$data = array();
foreach ($methods_info->content->delivery_methods as $method) 
{
    $methods = array('method_id' => $method->id, 'method_description' => $method->name);
    array_push($data, $methods);
}

foreach ($data as $dt) {
    Mage::getModel('basic/methods')
        ->setData($dt)
        ->save();
}