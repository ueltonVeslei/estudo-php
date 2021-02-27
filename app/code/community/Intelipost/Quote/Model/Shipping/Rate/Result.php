<?php

class Intelipost_Quote_Model_Shipping_Rate_Result
extends Mage_Shipping_Model_Rate_Result
{
    public function sortRatesByPrice()
    {
        if (!is_array($this->_rates) || !count($this->_rates)) return;

        if (!Mage::getStoreConfig('carriers/intelipost/use_order_price')) return;
        
		$order_price_by = Mage::getStoreConfig('carriers/intelipost/order_price_by');

		if (version_compare(phpversion(), '5.3.0', '<')===true) 
		{
			if($order_price_by == 'desc')
			{			
				usort($this->_rates, create_function('$a,$b', 'return $a->getPrice() < $b->getPrice();'));
			}
			else
			{
				usort($this->_rates, create_function('$a,$b', 'return $a->getPrice() > $b->getPrice();'));
			}
		}		
		
        return $this;
    }
}

