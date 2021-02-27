<?php
class Controller_Login extends Controller {

	protected function _post() {
		if($post = (array)$this->getData('body')) {

			// Mage::getSingleton("core/session");
			$session = Mage::getSingleton('customer/session');

		    try {
		        $session->login($post['email'], $post['password']);
		        $session->setCustomerAsLoggedIn( $session->getCustomer() );
		        $this->setResponse('status',Standard::STATUS200);
				$customer = Mage::getModel('customer/customer')->load($session->getCustomer()->getId());

				$this->setResponse('data', [
					'customer' => $customer->toArray(),
					'token' => $session->getCustomer()->getId()
				]);
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
		if($customerEMAIL = $this->getData('EMAIL')) {
			$customer = Mage::getModel("customer/customer"); 
			$customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
			$customer->loadByEmail($customerEMAIL);

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
