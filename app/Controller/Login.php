<?php
class Controller_Login extends Controller {

	// Realiza login do cliente
	protected function _post() {
		if($post = (array)$this->getData('body')) {

			Mage::getSingleton("core/session", array("name" => "frontend"));
			$session = Mage::getSingleton('customer/session');
			$session->start();

		    try {
		        $session->login($post['email'], $post['password']);
		        $session->setCustomerAsLoggedIn( $session->getCustomer() );
		        $this->setResponse('status',Standard::STATUS200);
						$customer = Mage::getModel('customer/customer')->load( $session->getCustomer()->getId() );
						$this->setResponse('data',array('customer' => $customer->toArray()));
		    }
		    catch( Exception $e )
		    {
		        $this->setResponse('status',Standard::STATUS500);
				$this->setResponse('data','Ocorreu um erro: ' . $e->getMessage());
		    }
		} else {
			$this->setResponse('status',Standard::STATUS404);
			$this->setResponse('data','Dados não informados');
		}
	}

	// Recuperação de senha
	protected function _get() {
		if($customerID = $this->getData('ID')) {
			$customer = Mage::getModel('customer/customer')->load($customerID);
	        if ($customer->getId()) {
	            try {
	                $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
	                $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
	                $customer->sendPasswordResetConfirmationEmail();
	                $this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data','Link enviado com sucesso');
	            } catch (Exception $exception) {
	                $this->setResponse('status',Standard::STATUS500);
					$this->setResponse('data','Ocorreu um erro: ' . $e->getMessage());
	            }
	        }
		} else {
			$this->setResponse('status',Standard::STATUS404);
			$this->setResponse('data','Dados não informados');
		}
	}

}
