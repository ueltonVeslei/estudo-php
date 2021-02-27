<?php

/**
 * @category    Intelipost
 * @package     Intelipost_Quote
 * @copyright   Copyright (c) 2014 Intelipost. (http://www.intelipost.com.br)
 */ 
class Intelipost_Quote_Model_Quote_Address_Shipping_Rate extends Mage_Core_Model_Abstract
{

	protected $_collection;

    protected function _construct()
    {
        $this->_init('quote/quote_address_shipping_rate');
    }

    public function importShippingRate($method, $address_id, $intelipostQuoteId)
    {
    	$currentTime = Varien_Date::now();
    	$collection_data = $this->_getCollection($address_id);
    	$data_exists = false;

    	if (count($collection_data) > 0)
    	{
	    	foreach ($collection_data as $single_data) 
	    	{
	    		if ($single_data['method'] == $this->getMethod($method->delivery_method_id))
	    		{
	    			$data_exists = true;

	    			$data = array(
	    				'rate_id'	 									=> $single_data['rate_id'],
    					'code'       									=> $this->getCode($method->delivery_method_id),
    					'updated_at'								 	=> $currentTime,
                        'description'                                   => Mage::helper('quote')->getCustomizeCarrierTitle($method->delivery_method_name, $method->delivery_estimate_business_days),
    					'method'    									=> $this->getMethod($method->delivery_method_id),
    					'price'      									=> $method->final_shipping_cost,
    					'intelipost_cost'       						=> $method->provider_shipping_cost,
    					'intelipost_estimated_delivery_business_days'	=> $method->delivery_estimate_business_days,
    					'intelipost_quote_id'							=> $intelipostQuoteId
    				);
	    		}
	    	}
    	}

    	if (!$data_exists)
    	{
    		$data = array(
    					'address_id' 									=> $address_id,
    					'code'       									=> $this->getCode($method->delivery_method_id),
    					'created_at'									=> $currentTime,
    					'updated_at' 									=> $currentTime,
                        'description'                                   => Mage::helper('quote')->getCustomizeCarrierTitle($method->delivery_method_name, $method->delivery_estimate_business_days),
    					'method'     									=> $this->getMethod($method->delivery_method_id),
    					'price'      									=> $method->final_shipping_cost,
    					'intelipost_cost'       						=> $method->provider_shipping_cost,
    					'intelipost_estimated_delivery_business_days'	=> $method->delivery_estimate_business_days,
    					'intelipost_quote_id'							=> $intelipostQuoteId
    				);
    	}    	

    	$this->addData($data);

    	$this->save();
    }

    protected function getCode($delivery_method_id)
    {
    	$prefix = $this->getPrefix('code') . $delivery_method_id;
    	return $prefix;
    }

    protected function getMethod($delivery_estimate_business_days)
    {
    	$prefix = $this->getPrefix('method') . $delivery_estimate_business_days;
    	return $prefix;
    }

    protected function getPrefix($column)
    {
    	if ($column == 'code')
    	{
    		return 'intelipost_';
    	}
    	else
    	{
    		return '';
    	}
    }

    protected function _getCollection($address_id)
    {
        $expression = 'address_id';

    	$this->_collection = Mage::getModel('quote/quote_address_shipping_rate')->getCollection()
    									->addFieldToFilter($expression, $address_id);    	

    	return $this->_collection->getData();
    }

}
