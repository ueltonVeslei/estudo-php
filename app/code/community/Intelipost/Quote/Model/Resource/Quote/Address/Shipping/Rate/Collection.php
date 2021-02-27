<?php

/**
 * @category    Intelipost
 * @package     Intelipost_Quote
 * @copyright   Copyright (c) 2014 Intelipost. (http://www.intelipost.com.br)
 */ 
class Intelipost_Quote_Model_Resource_Quote_Address_Shipping_Rate_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('quote/quote_address_shipping_rate');
    }

}
