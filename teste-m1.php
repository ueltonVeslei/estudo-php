<?php

//Carrega os modulos da vendor 
require_once('app/Mage.php');


Mage::app()->setCurrentStore('default');

//Carrega a order
$order = Mage::getModel('sales/order')->loadByIncrementId(104022175);
echo 'Dados do pedido: ' . json_encode($order->getData());

$items = $order->getAllItems();
$rules = array();

function _loadRule($id)
{
    if (!isset($rules[$id])) {
        $rules[$id] = Mage::getModel('salesrule/rule')->load($id);
    }
    return $rules[$id];
}

function _setItemOriginalPrice($items) {
    echo '
    Número de items: ' . count($items);

    foreach ($items as $item) {
        $buyRequest = $item->getBuyRequest();
        $rule = null;
        if (isset($buyRequest['options']['ampromo_rule_id'])) {
            echo '
            Entrou na regra 1, item: ' . $item->getId();
            $rule = _loadRule($buyRequest['options']['ampromo_rule_id']);
        } elseif (isset($buyRequest['ampromo_rule_id'])) {
            echo '
            Entrou na regra 2, item: ' . $item->getId();
            $rule = _loadRule($buyRequest['ampromo_rule_id']);
        }
        if(!empty($rule)) {
            echo '
            Entrou no if para setar o preço, item: ' . $item->getId();

            //$item->setOriginalPrice($item->getPrice());
        }
    } 
}


//onOrderPlaceBefore
_setItemOriginalPrice($items);
