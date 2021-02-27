<?php

class Intelipost_Quote_Model_Quote_Request
{
    private $_shippingMethod;
    private $_shippingDescription;

    public function processRequestQuote($orderId, $currentIntelipostQuoteId)
    {
        $order       = Mage::getModel('sales/order')->load($orderId);
        $adress      = $order->getShippingAddress();
        $postcode    = $adress->getData('postcode');
        $price       = $order->getData('base_subtotal');
        $quoteMethod = Mage::getStoreConfig ('intelipost_basic/settings/quote_method');

        $shippingData = Mage::getModel('quote/carrier_intelipost_data');
        $shippingData->checkZipCodeOrigin($postcode);
        $shippingData->checkZipCodeDest($postcode);
        $shippingData->setPackagePrice($price);

        if ($quoteMethod == 'product') 
        {
            $shippingData->setQuoteProduct($order->getAllItems());
            $requestQuote = Mage::getModel('quote/request_product'); 
        }
        else {
            $shippingData->getDimension()->calcItemsDimension($order->getAllItems());
            $requestQuote = Mage::getModel('quote/request_dimension'); 
        }       

        $intelipostQuote = $this->doQuote($shippingData, $requestQuote);

        if ($intelipostQuote->isQuoteSuccess())
        {
            $newIntelipostQuoteId = $intelipostQuote->getQuoteId();         
            $methods = $intelipostQuote->getMethods(false);
        
            $deliveryDays = $this->getDeliveryDays($order->getShippingDescription());
            $quoteMethods = $this->getMethods($methods, $order->getShippingMethod(), $deliveryDays, $intelipostQuote);

            if (count($quoteMethods) == 0)
            {
                throw new Mage_Shipping_Exception(Mage::helper('quote')->__('Problem occurred while trying to update quote. Try again.'));              
            }
            else
            {
                $volumes = Mage::helper('basic')->getOrderQtyVolumes($orderId);

                if ($currentIntelipostQuoteId)
                {
                    $intelipost_order = Mage::getModel('basic/orders')->load($order->getId(), 'order_id');  
                            
                    foreach ($quoteMethods as $singleMethod)
                    {
                        $shippingMethod = 'intelipost_' . $singleMethod->delivery_method_id;

                        $this->setShippingMethod($shippingMethod, $newIntelipostQuoteId);
                        $this->setShippingDescription($singleMethod->description, $deliveryDays);

                        $data = array('order_id'  => $orderId,
                          'delivery_quote_id'     => $newIntelipostQuoteId,
                          'delivery_method_id'    => $singleMethod->delivery_method_id,
                          'delivery_business_day' => $deliveryDays,
                          'shipping_cost'         => $singleMethod->provider_shipping_cost,
                          'qty_volumes'           => $volumes,
                          'status'                => 'waiting');                        
                    }
                    
                    $intelipost_order->addData($data)->save();
                    //$order->save();
                }
                else
                {
                    $intelipost_order = Mage::getModel('basic/orders'); 
    
                    foreach ($quoteMethods as $singleMethod)
                    {
                        $deliveryDays = $deliveryDays != 100 ? $deliveryDays : $singleMethod->delivery_estimate_business_days;

                        $shippingMethod = 'intelipost_' . $singleMethod->delivery_method_id;

                        $this->setShippingMethod($shippingMethod, $newIntelipostQuoteId);
                        $this->setShippingDescription($singleMethod->description, $deliveryDays);
                        
                        $data = array('order_id'  => $orderId,
                          'delivery_quote_id'     => $newIntelipostQuoteId,
                          'delivery_method_id'    => $singleMethod->delivery_method_id,
                          'delivery_business_day' => $deliveryDays,
                          'shipping_cost'         => $singleMethod->provider_shipping_cost,
                          'qty_volumes'           => $volumes,
                          'status'                => 'waiting');
                    }
                    
                    $intelipost_order->addData($data)->save();
                    //$order->save();
                }                                       
            }           
        }
    }

    protected function getMethods($methods, $orderDescription, $deliveryDays, $intelipostQuote)
    {
        if ($orderDescription == Mage::helper('quote')->getFallbackMethod())
        {
            return $intelipostQuote->getMethodForFallback($methods, $deliveryDays);
        }
        else
        {
            return $intelipostQuote->getMethodForRequote($methods, $deliveryDays);
        }
    }

    protected function doQuote($shippingData, $requestQuote)
    { 
        $intelipostQuote = Mage::getModel('quote/class_intelipost_quote');
        $intelipostQuote->requestQuote($requestQuote->fetchQuoteRequest($shippingData, true), false, true);

        return $intelipostQuote;
    }

    protected function getDeliveryDays($shippingDescription)
    {
        preg_match_all('!\d+!', $shippingDescription, $matches);
        foreach ($matches as $key => $value) 
        {
            $deliveryDays = ($value) ? (int)$value[0] : 100;
        }
        return $deliveryDays;
    }

    public function getMethodId($method_id)
    {
        preg_match_all('!\d+!', $method_id, $matches);
        foreach ($matches as $key => $value) 
        {
            $id = ($value) ? (int)$value[0] : 100;
        }
        return $id;
    }

    public function getShippingMethod()
    {
        return $this->_shippingMethod;
    }

    public function setShippingMethod($shippingMethod, $newIntelipostQuoteId)
    {
        if (Mage::helper('quote')->getConfigData('concat_quote_id')) {
            $shippingMethod .= '_' . $newIntelipostQuoteId;
        }
        $this->_shippingMethod = $shippingMethod;
    }

    public function getShippingDescription()
    {
        return $this->_shippingDescription;
    }

    public function setShippingDescription($shippingDescription, $deliveryDays)
    {
        if (!Mage::helper('quote')->getConfigData('keep_method_description')) {
            $shippingDescription = Mage::helper('quote')->getConfigData('title') . ' - ' . Mage::helper('quote')->getCustomizeCarrierTitle($shippingDescription, $deliveryDays);
        }
        $this->_shippingDescription = $shippingDescription;
    }
}