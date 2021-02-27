<?php

class Intelipost_Quote_Model_Carrier_Intelipost     
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{  

const CODE = 'intelipost';
const FREE_SHIPPING = '_freeShipping';
protected $_code = self::CODE;
protected $_helper;
protected $_intelipostApi;
protected $_intelipostQuote;
protected $_shippingData;
protected $_freeShipping = self::FREE_SHIPPING;
protected $_isPremiumCostumerFreeShipping = false;
protected $_isLowerDeliveryDate = false;
protected $_intelipostRestrictedMsg = false;
protected $_usedScheduleDelivery = false;
protected $_orderMethods;

public function collectRates(Mage_Shipping_Model_Rate_Request $request)
{
    $rounded_prices = $this->getConfigData('rounded_prices');
    
    $result = Mage::getModel('shipping/rate_result');        
    
    $this->_shippingData = Mage::getModel('quote/carrier_intelipost_data');
    //$requestQuote = Mage::getModel('quote/request_quote'); 
    
    try
    {      
        if (!$this->isValidRequest($request)) return;

        $this->_shippingData->setPackageWeight($request->getPackageWeight(), $request->getAllItems());
        $this->_shippingData->setPackagePrice($request->getPackageValue());
        $this->_intelipostQuote = Mage::getModel('quote/class_intelipost_quote');
        
        $quoteMethod = Mage::getStoreConfig ('intelipost_basic/settings/quote_method');

        if ($quoteMethod == 'product') {
            $this->_shippingData->setQuoteProduct($request->getAllItems());
            $requestQuote = Mage::getModel('quote/request_product'); 
        }
        else {
            $this->_shippingData->getDimension()->calcItemsDimension($request->getAllItems());
            $requestQuote = Mage::getModel('quote/request_dimension'); 
        }
                 
        $this->_intelipostQuote->requestQuote($requestQuote->fetchQuoteRequest($this->_shippingData), $this->_isChanged ($request) ? false : true);

        $intelipostQuoteId = $this->_intelipostQuote->getQuoteId();

        if ($this->_intelipostQuote->isQuoteSuccess())
        {             
            $methods = $this->_intelipostQuote->getMethods($request->getFreeShipping());
            $useShippingRate = Mage::helper('quote')->getConfigData('shipping_rate');

            if (empty ($methods)) return $result;  

            $methods_qty = 0;
            if (Mage::getStoreConfig ('carriers/intelipost/choose_methods_qty'))
            {
                $methods_qty = Mage::getStoreConfig ('carriers/intelipost/methods_display_qty');
            }

            if ($useShippingRate)
            {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $address_id = $quote->getShippingAddress()->getAddressId();
            }
            
            $i = 1;

            if ($this->getHelper()->getConfigData('prazo_produto')) 
            {
                $this->_shippingData->setPrazoProdutos($request->getAllItems());
                $methods = $this->_intelipostQuote->fetchPrazoProduto($methods, $this->_shippingData->getPrazoProdutos());
            }            


            foreach($methods as $method)
            {
                if ($useShippingRate)
                {
                    $intelipost_rate = Mage::getModel('quote/quote_address_shipping_rate');
                    $intelipost_rate->importShippingRate($method, $address_id, $intelipostQuoteId);                   
                }

                if ($methods_qty != 0 && $methods_qty < $i) continue;

               // if (!is_int ($method->delivery_estimate_business_days)) continue;
                
                $result_method = Mage::getModel('shipping/rate_result_method'); 
                
                if ($rounded_prices) {
                    $roundValue = $rounded_prices == 'up' ? ceil($method->final_shipping_cost) : floor($method->final_shipping_cost);
                }
                
                if ($method->delivery_note != null && !$this->_intelipostRestrictedMsg && $this->getHelper()->getConfigData('use_restricted_area_msg')) 
                {
                    $restricted_msg = $this->getHelper()->getConfigData('restricted_msg') ? $this->getHelper()->getConfigData('restricted_msg') : $method->delivery_note;
                    $error = Mage::getModel('shipping/rate_result_error');
                    $error->setCarrier($this->_code)
                    ->setCarrierTitle($this->getConfigData('title'))
                    ->setErrorMessage($restricted_msg);

                    $result->append($error);              
                    $this->_intelipostRestrictedMsg = true;
                }

                $result_method->setCarrier                      ($this->_code); 
                $result_method->setCarrierTitle                 ($this->getConfigData('title')); 
                $result_method->setMethod                       ($method->delivery_method_id);                 
                $result_method->setMethodTitle                  ($this->getHelper()->getCustomizeCarrierTitle($method->description, $method->delivery_estimate_business_days, $method->delivery_method_id));                         
                $result_method->setMethodDescription            ($this->getHelper()->getCustomizeCarrierTitle($method->delivery_method_name, $method->delivery_estimate_business_days, $method->delivery_method_id));
                $result_method->setPrice                        ($rounded_prices ? $roundValue : $method->final_shipping_cost); 
                $result_method->setIntelipostCost                         ($method->provider_shipping_cost); 
                $result_method->setIntelipostEstimatedDeliveryBusinessDays($method->delivery_estimate_business_days);
                $result_method->setIntelipostQuoteId($intelipostQuoteId);
                
                $result->append($result_method);
                
                $i++;                       
            }

            
            if ($this->useScheduleDelivery())
            {
                $this->_usedScheduleDelivery = true;
                $scheduleMethod = $this->_intelipostQuote->fetchScheduledDelivery($methods);

                if ($useShippingRate)
                {
                    $intelipost_rate = Mage::getModel('quote/quote_address_shipping_rate');
                    $intelipost_rate->importShippingRate($scheduleMethod, $address_id, $intelipostQuoteId);                   
                }

                $result->append($this->appendScheduleMethod($scheduleMethod, $rounded_prices, $intelipostQuoteId));
            }
        }
        else
        {
            if ($this->_intelipostQuote->errors['key'] != 'quote.no.delivery.options') {
                $this->loadImported($result, $request);                              
            }
            else 
            {
                throw new Exception($this->_intelipostQuote->errors['text'], 1);                
            }

        }                        
    }
    catch(Exception $e)
    {
        Mage::log($e->getMessage());
        $this->_appendError($result, $e->getMessage());
    }
       
    return $result;            
}


public function useScheduleDelivery()
{
    $controller = Mage::app()->getRequest()->getControllerName();
    if ($this->getHelper()->getConfigData('use_scheduled_delivery') && !$this->_usedScheduleDelivery && strtoupper($controller) != 'CART')
    {
        return true;
    }

    return false;
}

public function appendScheduleMethod($method, $rounded_prices, $intelipostQuoteId)
{
    $roundValue = $rounded_prices == 'up' ? ceil($method->final_shipping_cost) : floor($method->final_shipping_cost);
    $result_method = Mage::getModel('shipping/rate_result_method');

    $result_method->setCarrier                      ($this->_code); 
    $result_method->setCarrierTitle                 ($this->getConfigData('title')); 
    $result_method->setMethod                       ($method->delivery_method_id);                 
    $result_method->setMethodTitle                  ($this->getHelper()->getCustomizeCarrierTitle($method->description, $method->delivery_estimate_business_days));                         
    $result_method->setMethodDescription            ($this->getHelper()->getCustomizeCarrierTitle($method->delivery_method_name, $method->delivery_estimate_business_days));
    $result_method->setPrice                        ($rounded_prices ? $roundValue : $method->final_shipping_cost); 
    $result_method->setIntelipostCost                         ($method->provider_shipping_cost); 
    $result_method->setIntelipostEstimatedDeliveryBusinessDays($method->delivery_estimate_business_days);
    $result_method->setIntelipostQuoteId($intelipostQuoteId);

    return $result_method;
}


public function getAllowedMethods()
{
     return array(
    '1'     =>  'Correios PAC',
    '2'     =>  'Correios Sedex',
    '3'     =>  'Correios E-Sedex',
    '4'     =>  'Total Express',
    '5'     =>  'Loggi',
    '8'     =>  'Direct E-Direct',
    '21'    =>  'Vialog Express',
    '138'   =>  'Motoboy Delivery',
    '10000' =>  'Premium Shipping'
    );
}

public function isValidRequest (Mage_Shipping_Model_Rate_Request $request)
{
    // if (!$this->getHelper()->isEnabled()) return;

    if (!$this->_shippingData->checkZipCodeOrigin($request->getDestPostcode()))
    {
        throw new Mage_Shipping_Exception($this->getHelper()->__('Please check zip code origins informed.'));
    }

    if (!$this->_shippingData->checkZipCodeDest($request->getDestPostcode())) 
    {
        throw new Mage_Shipping_Exception($this->getHelper()->__('Favor informar um cep válido.'));
    }    

    return true;
}

protected function _appendError(Mage_Shipping_Model_Rate_Result &$result, $message)
{
    $error = Mage::getModel('shipping/rate_result_error');
    $error->setCarrier($this->_code)
        ->setCarrierTitle($this->getConfigData('title'))
        ->setErrorMessage($message);

    $result->append($error);

    return $this;
}

protected function loadImported(& $result, $request)
{
    $fallback = (int) $this->getHelper()->getConfigData('fallback_lead_time');
    $import = Mage::getModel('quote/request_import');

    if ($import->requestImport($this->_shippingData->getPackageWeight(), $this->_shippingData->getDestZipCode()))
    {
        $price = $request->getFreeShipping() ? 0 : $import->_shipping_price;
        $method = Mage::getModel('shipping/rate_result_method');                         
            
        $method->setCarrier     ('intelipost'); 
        $method->setCarrierTitle('Intelipost'); 
        $method->setMethod      ('Fallback'); 
        $method->setMethodTitle ($this->getHelper()->getCustomizeCarrierTitle($this->getHelper()->getConfigData('fallback_frontend_title'), $fallback > 0 ? ($import->_number_of_days + $fallback) : $import->_number_of_days));
        $method->setMethodDescription($this->getHelper()->getCustomizeCarrierTitle($this->getHelper()->getConfigData('fallback_frontend_title'), $fallback > 0 ? ($import->_number_of_days + $fallback) : $import->_number_of_days));
        $method->setPrice       ($price); 
        $method->setCost        ($import->_shipping_price); 

        $result->append($method);
    }
    else
    {
        throw new Mage_Shipping_Exception('Não foi possível realizar a cotação do frete. Tente novamente');
    }
}

private function _isChanged (Mage_Shipping_Model_Rate_Request $request)
{
    if (Mage::helper('quote')->getConfigData('debug')) {
        return false;
    }
    
    $session_id = Mage::getSingleton ('core/session')->getEncryptedSessionId ();
    $cache_id = self::CODE . '_request_' . $session_id;
    $lifetime = Mage::getStoreConfig ('carriers/intelipost/lifetime');

    $search = array(
        'dest_postcode' => $this->removePostcodeFormat($request->getDestPostcode()),
        'package_weight' => $request->getPackageWeight (),
        'package_qty' => $request->getPackageQty (),
    );

    $cache = Mage::app()->getCache();
    $stored = unserialize ($cache->load ($cache_id));

    $is_changed = true;

    if (is_array ($stored))
    {
        $diff = array_diff ($stored, $search);
        if (!count ($diff)) $is_changed = false;
    }

    if ($is_changed)
    {
        $cache->save (serialize ($search), $cache_id, array(), intval ($lifetime) > 0 ? $lifetime * 60 : 60);
    }

    return $is_changed;
}

public function removePostcodeFormat($postcode)
{
    return str_replace('-', null, trim($postcode));
}

public function getHelper()
{
    if (!$this->_helper) $this->_helper = Mage::helper('quote');

    return $this->_helper;
}

}

