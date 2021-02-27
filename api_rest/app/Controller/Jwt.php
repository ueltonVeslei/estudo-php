<?php
class Controller_Jwt extends Controller {

	protected function _get() {
    if($token = $this->getData('TOKEN')) {
      $customer = Mage::getModel('customer/customer')->load($token);

      // Mage::getSingleton("core/session", array("name" => "frontend"));
      $session = Mage::getSingleton('customer/session');
	  $session->setCustomerAsLoggedIn( $customer );
      $this->setResponse('status', Standard::STATUS200);
      return $this->setResponse('data', $customer->toArray());
    }

    $this->setResponse('status', Standard::STATUS404);
    $this->setResponse('data', 'Dados não enviados');

	}

}
