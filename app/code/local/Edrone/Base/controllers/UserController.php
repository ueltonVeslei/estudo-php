<?php

class Edrone_Base_UserController extends Mage_Core_Controller_Front_Action {

  public function sessionDataAction(){
      header("Cache-Control: no-cache, max-age=0");
      $userData = [];
      $userData['email'] = "";
      $userData['first_name'] = "";
      $userData['last_name'] = "";
      $userData['country'] = "";
      $userData['city'] = "";
      $userData['phone'] = "";
      $userData['subscriber_status'] = "";
      if (Mage::getSingleton('customer/session')->isLoggedIn()) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $userData['email'] = $customer->getEmail();
        $userData['first_name'] = $customer->getFirstname();
        $userData['last_name'] = $customer->getLastname();
        if ($address = $customer->getDefaultShippingAddress()) {
            $userData['country'] = $address->getCountry();
            $userData['city'] = $address->getCity();
            $userData['phone'] = $address->getTelephone();
        }
        if(Mage::getSingleton('core/session')->getEdroneSubscriberStatus()) {
          $userData['subscriber_status'] = Mage::getSingleton('core/session')->getEdroneSubscriberStatus();
        } else {
          $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
          if ($subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
            $userData['subscriber_status'] = 1;
          } elseif ($subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
            $userData['subscriber_status'] = 0;
          }
          Mage::getSingleton('core/session')->setEdroneSubscriberStatus($userData['subscriber_status']);
        }
      }
      $this->getResponse()->setHeader('Content-type', 'application/json');
      $this->getResponse()->setBody(json_encode($userData));

  }
}
