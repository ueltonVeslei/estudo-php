<?php

class Edrone_Base_Block_Base extends Mage_Core_Block_Template
{

    /**
     * @var Edrone_Base_Helper_Config
     */
    private $configHelper;

    /**
     * @var array
     */
    protected $customerData = array();

    public function _construct()
    {        
        parent::_construct();

        $this->configHelper = Mage::helper('edrone/config');
    }

    /**
     * @return Edrone_Base_Helper_Config
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }
    /**
     * @return array
     */
    public function getCustomerData()
    {
        if(!count($this->customerData)) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->getLoggedCustomerData();
            } else {
                $this->getGuestCustomerData();
            }
        }
        return $this->customerData;
    }

    private function getLoggedCustomerData()
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $this->customerData['first_name'] = $customer->getFirstname();
        $this->customerData['last_name'] = $customer->getLastname();
        $this->customerData['email'] = $customer->getEmail();
        $sub = (Mage::getSingleton('customer/session')->getData('edrone-substatus'));
        $update_time = strtotime(Mage::getSingleton('customer/session')->getCustomer()->getData("updated_at"));
        if( (!is_array($sub)) || (is_null($sub)) || ($sub['time'] !==  $update_time )){
            $sub = array();
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
            if($subscriber){
                $sub['status']  = $this->customerData['subscriber_status'] = ( $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED ) ? 1 : 0;  
                $sub['time']    = $update_time;
                Mage::getSingleton('customer/session')->setData('edrone-substatus',$sub);
            }      
        }else{
            $this->customerData['subscriber_status'] = $sub['status'];
        }
        if ($address = $customer->getDefaultShippingAddress()) {
            $this->customerData['country'] = $address->getCountry();
            $this->customerData['city'] = $address->getCity();
            $this->customerData['phone'] = $address->getTelephone();
        } else {
            $this->customerData['country'] = '';
            $this->customerData['city'] = '';
            $this->customerData['phone'] = '';
        }

        $this->customerData['is_logged_in'] = 1;
    }

    private function getGuestCustomerData()
    {
        $this->customerData['first_name'] = '';
        $this->customerData['last_name'] = '';
        $this->customerData['email'] = '';
        $this->customerData['country'] = '';
        $this->customerData['city'] = '';
        $this->customerData['phone'] = '';

        $quote = Mage::getModel('checkout/cart')->getQuote();
        $address = $quote->getBillingAddress();

        if($address) {
            $this->customerData['first_name'] = $address->getFirstname();
            $this->customerData['last_name'] = $address->getLastname();
            $this->customerData['email'] = $address->getEmail();
            $this->customerData['country'] = $address->getCountry();
            $this->customerData['city'] = $address->getCity();
            $this->customerData['phone'] = $address->getTelephone();
        }
        $this->customerData['subscriber_status'] = '';
        $this->customerData['is_logged_in'] = 0;
    }
}