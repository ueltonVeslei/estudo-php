<?php

class Intelipost_Quote_Model_Class_Intelipost_Quote
// extends Varien_Object
{

protected $methods;
protected $api;
protected $idCotacao;
public $_curlError = false;
protected $quoteSuccess;
public $errors = array();

public function requestQuote($requestData, $cached = true, $requote = false)
{
    if ($cached && $this->_readyCache ()) return;
    
    $quoteMethod = Mage::getStoreConfig ('intelipost_basic/settings/quote_method') == 'product' ? 'quote_by_product' : 'quote' ;

    Mage::helper('basic')->setVersionControlData(Mage::helper('quote')->getModuleName(), 'quote');
    $this->api = Mage::getModel('basic/intelipost_api');
    $this->api->apiRequest(Intelipost_Basic_Model_Intelipost_Api::POST, $quoteMethod, $requestData, Mage::helper('basic')->getVersionControlModel());
    if (!$this->api->_hasErrors)
    {
        $this->methods = $this->api->apiResponseToObject();
        $this->idCotacao = $this->methods->content->id;
        $this->quoteSuccess = true;

        if ($requote)
        {
            $this->saveRequoteShippingRates();
        }            

        $this->_saveCache (array(
            'methods' => $this->methods,
            'quote_id' => $this->idCotacao,
            'success' => $this->quoteSuccess,
        ));
    } 
    else
    {
        if ($this->api->_curlError) {
            $this->_curlError = true;
            $this->quoteSuccess = false;
            $this->errors = array('key' => 'api.problem', 'text' => 'API connection problem');
        }
        else {
            $apiError = $this->api->apiResponseToObject();
            $this->errors = array('key' => $apiError->messages[0]->key, 'text' => $apiError->messages[0]->text);
            $this->quoteSuccess = false;
        }
    }
}

public function saveRequoteShippingRates()
{
    $methods = $this->getMethods(false);
    $currentTime = Varien_Date::now();

    foreach ($methods as $method) 
    {
        $rate = Mage::getModel ('quote/quote_address_shipping_rate');
        $rate->setAddressId(0);
        $rate->setCreatedAt($currentTime);
        $rate->setUpdatedAt($currentTime);
        $rate->setCode('intelipost_' . $method->delivery_method_id);
        $rate->setDescription(Mage::helper('quote')->getCustomizeCarrierTitle($method->delivery_method_name, $method->delivery_estimate_business_days));
        $rate->setMethod($method->delivery_method_id);
        $rate->setPrice($method->final_shipping_cost);
        $rate->setIntelipostQuoteId($this->idCotacao);
        $rate->setIntelipostEstimatedDeliveryBusinessDays($method->delivery_estimate_business_days);
        $rate->setIntelipostCost($method->provider_shipping_cost);

        $rate->save(); 
    }            
}

public function getQuoteId ()
{
    return $this->idCotacao;
}

public function isQuoteSuccess()
{
    return $this->quoteSuccess;
}

public function getMethods($freeShipping)
{
    $free_shipping_method = Mage::getStoreConfig ('carriers/intelipost/free_shipping_method');

    $methodsToAdd = array();

    if (!$this->methods) return;

    foreach ($this->methods->content->delivery_options as $method) 
    {   
        $methodsToAdd[$method->delivery_method_id] = $method;                
    }

    $lower_price = 0;    
    $lower_price_id = 0;
    $lower_cost  = 0;
    $lower_cost_id = 0;
    $lower_delivery = 0;
    $lower_delivery_id = 0;
    $greater_delivery = 0;
    $greater_delivery_id = 0;
    $freeShippingOnlyFallback = Mage::helper('quote')->getConfigData('free_shipping_fallback');

    foreach ($methodsToAdd as $id => $child)
    {
        if ($child->delivery_method_id == $free_shipping_method && $freeShipping && !$freeShippingOnlyFallback)
        {
            $object = $methodsToAdd [$id];
            $object->final_shipping_cost = 0;
            $object->description = Mage::helper('quote')->getConfigData('free_shipping_text') != "" ? Mage::helper('quote')->getConfigData('free_shipping_text') : $child->description;

            continue;
        }
        else
        {
            if (!strcmp ($free_shipping_method, 'lower-price'))
            {
                $final_shipping_cost = $child->final_shipping_cost;
                if (!$lower_price || ($lower_price && $final_shipping_cost < $lower_price))
                {
                    $lower_price = $final_shipping_cost;
                    $lower_price_id = $id;
                }
            }
            else if (!strcmp ($free_shipping_method, 'lower-cost'))
            {
                $provider_shipping_cost = $child->provider_shipping_cost;
                if (!$lower_cost || ($lower_cost && $provider_shipping_cost < $lower_cost))
                {
                    $lower_cost = $provider_shipping_cost;
                    $lower_cost_id = $id;
                }
            }
            else if (!strcmp ($free_shipping_method, 'greater_delivery_date'))
            {
                $delivery_estimate_business_days = $child->delivery_estimate_business_days;
                if (!$greater_delivery || ($greater_delivery && $delivery_estimate_business_days > $greater_delivery))
                {
                    $greater_delivery = $delivery_estimate_business_days;
                    $greater_delivery_id = $id;
                }
            }
            else if (!strcmp ($free_shipping_method, 'lower_delivery_date'))
            {
                $delivery_estimate_business_days = $child->delivery_estimate_business_days;
                if (!$lower_delivery || ($lower_delivery && $delivery_estimate_business_days < $lower_delivery))
                {
                    $lower_delivery = $delivery_estimate_business_days;
                    $lower_delivery_id = $id;
                }
            }
            else
            {   
                $object = $methodsToAdd [$id];
                
                if ($object->final_shipping_cost == 0 && Mage::helper('quote')->getConfigData('free_shipping_text') != "")
                {
                    $object->description = Mage::helper('quote')->getConfigData('free_shipping_text');   
                }
            }
        }
    }

    if ($freeShipping && ($lower_price_id || $lower_delivery_id || $lower_cost || $greater_delivery) && !$freeShippingOnlyFallback)
    {
        $methodId = $lower_price_id > 0 ? $lower_price_id : $lower_cost;
        if ($methodId == 0)
        {
            $methodId = $lower_delivery_id > 0 ? $lower_delivery_id : $greater_delivery_id;
        }

        $object = $methodsToAdd [$methodId];
        $object->final_shipping_cost = 0;
        $object->description = Mage::helper('quote')->getConfigData('free_shipping_text') != "" ? Mage::helper('quote')->getConfigData('free_shipping_text') : $child->description;
    }

    if (count($methodsToAdd) == 1 && Mage::helper('quote')->getConfigData('copy_single_method'))
    {
        foreach ($methodsToAdd as $m) 
        {
            $c = clone($m);

            if (Mage::helper('quote')->getConfigData('extra_time_economic_express'))
            {
                $m->delivery_estimate_business_days += Mage::helper('quote')->getConfigData('extra_time_economic_express');
            }           

            if (Mage::helper('quote')->getConfigData('extra_value_economic_express'))
            {
                $c->delivery_method_id += Mage::helper('quote')->getConfigData('express_method_id_prefix');
                $c->description = Mage::helper('quote')->getConfigData('title_for_express');
                $c->final_shipping_cost = $m->provider_shipping_cost + Mage::helper('quote')->getConfigData('extra_value_economic_express');
            }  

            $methodsToAdd[$m->delivery_method_id] = $m;       
        }

        array_push($methodsToAdd, $c);
    }
    
    $varien_object = new Varien_Object($methodsToAdd);
    Mage::dispatchEvent('intelipost_quote_methods_returned', array('methods'=>$varien_object));
    
    return $varien_object->getData();
}

/*
public function fetchValorFreteEmbutido($methods, $valorEmbutido)
{
    if (empty($valorEmbutido) || $valorEmbutido == 0) {
        return $methods;
    }

    $methodsToAdd = array();
    foreach($methods as $method)
    {
        $method->final_shipping_cost = ($method->final_shipping_cost <= $valorEmbutido) ? 0 : ($method->final_shipping_cost - $valorEmbutido);
        $methodsToAdd[$method->delivery_method_id] = $method;
    }

    return $methodsToAdd;
}*/

public function getMethodForFallback($quoteMethods, $fallbackDeliveryDays)
{
    $methodsToAdd = array();    

    foreach($quoteMethods as $method)
    {        
        if ($method->delivery_estimate_business_days <= $fallbackDeliveryDays)
        {
            if (count($methodsToAdd) == 0)
            {
                $methodsToAdd[$method->delivery_method_id] = $method;
            }
            else
            {
                foreach ($methodsToAdd as $singleMethod) 
                {
                    if ($singleMethod->delivery_estimate_business_days > $method->delivery_estimate_business_days)
                    {
                        unset($methodsToAdd[$singleMethod->delivery_method_id]);
                        $methodsToAdd[$method->delivery_method_id] = $method;
                    }
                    else if ($singleMethod->final_shipping_cost > $method->final_shipping_cost)
                    {
                        unset($methodsToAdd[$singleMethod->delivery_method_id]);
                        $methodsToAdd[$method->delivery_method_id] = $method;
                    }
                }
            }
        }
    }

    return $methodsToAdd;
}

public function getMethodForRequote($quoteMethods, $fallbackDeliveryDays)
{
    $methodsToAdd = array();    

    foreach ($quoteMethods as $key => $row) {
        $result[$key]  = $row->provider_shipping_cost;
     }
    array_multisort($result, SORT_ASC, $quoteMethods);
    
    foreach($quoteMethods as $method)
    {        
        if ($method->delivery_estimate_business_days <= $fallbackDeliveryDays)
        {
            if (count($methodsToAdd) == 0)
            {
                $methodsToAdd[$method->delivery_method_id] = $method;
            }
            elseif (Mage::getStoreConfig('carriers/intelipost/requote_auto_select_method') == 'cheapest_cost')
            {
                foreach ($methodsToAdd as $singleMethod) 
                {
                    if ($singleMethod->provider_shipping_cost > $method->provider_shipping_cost)
                    {
                        unset($methodsToAdd[$singleMethod->delivery_method_id]);
                        $methodsToAdd[$method->delivery_method_id] = $method;
                    }                    
                }
            }
            elseif (Mage::getStoreConfig('carriers/intelipost/requote_auto_select_method') == 'cheapest_price')
            {
                foreach ($methodsToAdd as $singleMethod) 
                {
                    if ($singleMethod->final_shipping_cost > $method->final_shipping_cost)
                    {
                        unset($methodsToAdd[$singleMethod->delivery_method_id]);
                        $methodsToAdd[$method->delivery_method_id] = $method;
                    }                    
                }
            }
            elseif (Mage::getStoreConfig('carriers/intelipost/requote_auto_select_method') == 'fastest')
            {
                foreach ($methodsToAdd as $singleMethod) 
                {
                    Mage::log('fastest');
                    if ($singleMethod->delivery_estimate_business_days > $method->delivery_estimate_business_days)
                    {
                        unset($methodsToAdd[$singleMethod->delivery_method_id]);
                        $methodsToAdd[$method->delivery_method_id] = $method;
                    }                    
                }
            }
        }
    }

    return $methodsToAdd;
}

public function fetchScheduledDelivery($methods)
{

    foreach ($methods as $key => $row) {
        $result[$key]  = $row->provider_shipping_cost;
     }

    array_multisort($result, SORT_ASC, $methods);

    $i = 0;

    foreach($methods as $method)
    {
        if ($i > 0) break;

        $scheduled_method = clone($method);
        $scheduled_method->description .= ' - Agendada';
        $scheduled_method->delivery_method_id .= '_agendada';
        //$methods[$scheduled_method->delivery_method_id] = $scheduled_method;
        $i++;
    }

    return $scheduled_method;
}

public function fetchPrazoProduto($methods, $prazoProduto)
{
    if (empty($prazoProduto) || $prazoProduto == 0) {
        return $methods;
    }

    $methodsToAdd = array();
    foreach($methods as $method)
    {
        $method->delivery_estimate_business_days += $prazoProduto;
        $methodsToAdd[$method->delivery_method_id] = $method;
    }

     return $methodsToAdd;
}

private function _saveCache ($response)
{
    $cache_id = $this->_getCacheId ();
    $lifetime = Mage::getStoreConfig ('carriers/intelipost/lifetime');

    $cache = $this->_getCache ();
    $cache->save (serialize ($response), $cache_id, array(), intval ($lifetime) > 0 ? $lifetime * 60 : 60);
}

private function _readyCache ()
{
    $cache_id = $this->_getCacheId ();
    $cache = $this->_getCache ();
    $result = unserialize($cache->load ($cache_id));

    if (is_array ($result) && count ($result))
    {
        if (!$result ['success'] || !Mage::helper('quote')->getConfigData('use_cache') || Mage::helper('quote')->getConfigData('debug'))
        {
            $cache->remove ($cache_id);

            return;
        }

        $this->methods = $result ['methods'];
        $this->idCotacao = $result ['quote_id'];
        $this->quoteSuccess = $result ['success'];

        return true;
    }
}

private function _getCache ()
{
    return Mage::app()->getCache();
}

private function _getCacheId ()
{
    $session_id = Mage::getSingleton ('core/session')->getEncryptedSessionId ();
    $cache_id = Intelipost_Quote_Model_Carrier_Intelipost::CODE . '_response_' . $session_id;

    return $cache_id;
}

}

