<?php

class Intelipost_Quote_Model_Request_Dimension
// extends Varien_Object
{

public $origin_zip_code;
public $skip_return_modes;
public $destination_zip_code;
public $volumes = array();
public $additional_information = array();
public $identification = array();

public function fetchQuoteRequest($shippingData, $isRequote = false)
{            
    $this->origin_zip_code = $shippingData->getOriginZipCode();
    $this->destination_zip_code = $shippingData->getDestZipCode();        

    if (Mage::helper('quote')->getConfigData('skip_return_modes') && $isRequote) {
        $this->skip_return_modes = true;
    }

    $this->fetchVolume($shippingData);
    
    if ($shippingData->hasPrazoProdutos()) {
        $this->fetchAdditionalInformation($shippingData);
    }

    $this->fetchIdentification();

    return $this;

}

private function fetchVolume($shippingData)
{
    $boxAdditionlWeigth = Mage::getStoreConfig('intelipost_basic/quote_volume/box_additional_weigth');
    $weightUnit = Mage::getStoreConfig('intelipost_basic/settings/weight_unit');
    $boxAdditionlWeigth = !strcmp ($weightUnit, 'kg') ? $boxAdditionlWeigth : ($boxAdditionlWeigth / 1000);

    if (Mage::getStoreConfig('intelipost_basic/quote_volume/advanced_vol_calc') != 'no')
    {            
        $package = $shippingData->getDimension()->getPackages();
        
        for ($i = 0; $i < count($package); $i++)
        {
            $volume = $this->_getBasicVolume ();
            $volume->fetchMultiVolumeRequest($package[$i], $boxAdditionlWeigth);

            array_push($this->volumes, $volume);
        }
        
    }
    else
    {
        $volume = $this->_getBasicVolume ();
        $volume->fetchVolumeRequest($shippingData, $boxAdditionlWeigth);

        array_push($this->volumes, $volume);
    }

    return $this;
}

private function fetchAdditionalInformation($shippingData)
{
    

    $this->additional_information = array(  "lead_time_business_days" => $shippingData->getPrazoProdutos(),
                                            "client_type" => $this->getCustomerGroup(),
                                            "sales_channel" => $this->getStoreName());
    if ($this->getClientId())
    {
        $this->additional_information['tax_id'] = $this->getClientId();
    }                                            

    return $this;
}

private function fetchIdentification()
{
    $currentUrl = htmlspecialchars(Mage::helper('core/url')->getCurrentUrl());
    $this->identification = array(  'session'   => Mage::getSingleton("core/session")->getEncryptedSessionId(),  
                                    'ip'        => $_SERVER['REMOTE_ADDR'],
                                    'page_name' => $this->getPageName(),
                                    'url'       => $currentUrl);

    return $this;
}

private function _getBasicVolume ()
{
    return Mage::getModel ('basic/request_volume');
}

private function getPageName()
{
    if ($this->getStore()->isAdmin())
    {
        return 'admin';
    }

    $controller = Mage::app()->getRequest()->getControllerName();
    $route = Mage::app()->getRequest()->getRouteName();

    if (strtoupper($route) == 'CHECKOUT')
    {
        if (strtoupper($controller) == 'CART')
        {
            return 'cart';
        }

        if (strtoupper($controller) == 'ONEPAGE')
        {
            return 'checkout';
        }
    }

    if (strtoupper($route) == 'ONEPAGECHECKOUT')
    {
        return 'checkout';
    }

    if (strtoupper($route) == 'CUSTOMER')
    {
        return 'customer';
    }

    if (strtoupper($route) != 'CHECKOUT' && strtoupper($route) != 'ONEPAGECHECKOUT')
    {
        return 'product';
    }
}

private function getCustomerGroup()
{
    if ($this->getStore ()->isAdmin ())
    {
        $roleId = Mage::getSingleton('adminhtml/session_quote')->getQuote ()->getCustomerGroupId ();
    }
    else
    {
        $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
    }

    $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
    $role = strtolower($role);

    return $role;
}

private function getStoreName()
{
    return $this->getStore()->getName();
}

private function getStore()
{
    return Mage::app()->getStore();
}

private function getClientId()
{
    $client_id = Mage::helper('quote')->getConfigData('client_id');
    $client_value = false;

    if (!$client_id)
    {
        return $client_value;
    }

    $customerData = 0;
    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
        $customerData = Mage::getSingleton('customer/session')->getCustomer();
    }

    switch ($client_id) {
        case 'cpf':
            $cpf = Mage::helper('quote')->getConfigData('cpf_attr');            
            if ($customerData && $customerData->getData($cpf)) 
            {
                $cpf_data = $customerData->getData($cpf);
                $data = str_replace('.', '', $cpf_data);
                $data = str_replace('-', '', $cpf_data);

                $client_value = $data;
            }
            break;
        
        case 'cnpj':
            $cnpj = Mage::helper('quote')->getConfigData('cnpj_attr');        
            if ($customerData && $customerData->getData($cnpj)) 
            {
                $cnpj_data = $customerData->getData($cnpj);
                $data = str_replace('.', '', $cnpj_data);
                $data = str_replace('-', '', $cnpj_data);
                $data = str_replace('/', '', $cnpj_data);

                $client_value = $data;                
            }
            break;
    }

    return $client_value;
}

}

