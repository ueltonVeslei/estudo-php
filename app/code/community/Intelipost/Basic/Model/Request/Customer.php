<?php

class Intelipost_Basic_Model_Request_Customer
// extends Varien_Object
{

    /**
     * @var string
     */
    public $first_name;

    /**
     * @var string
     */
    public $last_name;
   
    public $email;

    public $phone;

    public $cellphone = "";

    public $is_company;

    public $federal_tax_payer_id;

    //public $state_tax_payer_id;
   
    public $shipping_address;

    public $shipping_number;

    public $shipping_additional;

    public $shipping_reference;

    public $shipping_quarter;

    public $shipping_city;

    public $shipping_state;

    public $shipping_zip_code;

    public $shipping_country;

    /**
     * fetch collected data to quote     
     *
     * @param Intelipost_Shipping_Model_Carrier_Shipping_Data
     * @return Intelipost_Model_Request_Quote
     */    
    public function fetchRequest($order, $trackRequest)
    {            
        $this->fillCostumerData($order, $trackRequest);
        $this->fillShipmentData($order);       

        return $this;

    }

    public function fillCostumerData($order, $trackRequest)
    {      
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $federal_tax_payer_id_attribute = Mage::getStoreConfig('intelipost_push/attributes/federal_tax_payer_id');
        $this->federal_tax_payer_id = $customer->getData($federal_tax_payer_id_attribute);
        $this->federal_tax_payer_id = $this->federal_tax_payer_id ? $this->federal_tax_payer_id : $order->getData('customer_taxvat');

        $this->first_name = $order->getShippingAddress()->getFirstname() ? $order->getShippingAddress()->getFirstname() : $order->getCustomerFirstname();
        $this->last_name = $order->getShippingAddress()->getFirstname()  ? $order->getShippingAddress()->getLastname()  : $order->getCustomerLastname();
        $this->email = $order->getShippingAddress()->getEmail()          ? $order->getShippingAddress()->getEmail()     : $order->getCustomerEmail();
        $this->is_company = $order->getBillingAddress()->getCompany() ? 'true' : 'false';        
        //$this->state_tax_payer_id = $trackRequest['state_tax_payer_id'];
        $this->phone = $order->getShippingAddress()->getTelephone() ? $order->getShippingAddress()->getTelephone() : $order->getBillingAddress()->getTelephone();
        $this->cellphone = $order->getShippingAddress()->getFax() ? $order->getShippingAddress()->getFax() : $order->getBillingAddress()->getFax();
        
        return $this;        
    }

    public function fillShipmentData($order)
    {
        //$address_number = 'get' . Mage::helper('quote')->getConfigData('address_number') . '()';

        $this->getAddressData($order->getShippingAddress()->getStreet(), $order);
        $this->shipping_city = $order->getShippingAddress()->getCity();
        $this->shipping_state = $order->getShippingAddress()->getRegion();
        $this->shipping_zip_code = $order->getShippingAddress()->getPostcode();
        $this->shipping_country = $order->getShippingAddress()->getCountryId();
        // $this->shipping_quarter = $order->getShippingAddress()->getAddressQuarter();
        // $this->shipping_number = $this->getAddressNumber($order);

        return $this;
    }

    public function getAddressData($addressArray, $order)
    {
        $this->shipping_address = (isset($addressArray[0]) && $addressArray[0] != '') ? $addressArray[0] : "";
        $this->shipping_number = (isset($addressArray[1]) && $addressArray[1] != '') ? $addressArray[1] : $this->getAddressNumber($order);
        $this->shipping_additional = (isset($addressArray[2]) && $addressArray[2] != '') ? $addressArray[2] : "";
        $this->shipping_quarter = (isset($addressArray[3]) && $addressArray[3] != '') ? $addressArray[3] : "";

        if (!$this->shipping_number) {
            $this->shipping_number = 's/n';
        }
        return $this;
    }

    public function getAddressNumber($order)
    {
        $number = explode(',', $this->shipping_address);

        $retorno = "";

        if (count($number) > 1)
        {
            if (is_numeric(trim($number[1])))
            {
                $retorno = trim($number[1]);
            }            
        }
        else
        {
            if ($order->getShippingAddress()->getAddressNumber())
            {
                $retorno = $order->getShippingAddress()->getAddressNumber();
            }
        }
        
        return $retorno;
    }
} 

