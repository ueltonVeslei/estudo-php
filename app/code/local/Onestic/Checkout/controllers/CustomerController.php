<?php  
class Onestic_Checkout_CustomerController extends Mage_Core_Controller_Front_Action {  

	/**
	 * See if customer already exists
	 */
	public function existsAction() {
		//$result = new stdClass();
		$user = $this->getRequest()->getParam( 'user' );
		$data = array('result' => false);
	
		if (strpos($user, '@') === false) {
			// é CPF/CNPJ
			$result = Mage::helper( 'onestic_checkout' )->customerTaxvatExists( $user );
			Mage::getSingleton('core/session')->setCurrentTaxvat($user);
		} else {
			$result = Mage::helper( 'onestic_checkout' )->customerEmailExists( $user );
			Mage::getSingleton('core/session')->setCurrentEmail($user);
		}
	
		if ($result) {
			$data['result'] = true;
		}
	
		$this->getResponse()->setBody(Zend_Json::encode($data));
	}
	
	/**
	 * See if supplied email already exists
	 */
	public function emailExistsAction() {
		$validator = new Zend_Validate_EmailAddress();
		$email = $this->getRequest()->getParam( 'email' );
		$data = array('result' => 'clean');
	
		if ($email && $email != '') {
			if (!$validator->isValid($email)) {
	
			} else {
				if (Mage::helper( 'onestic_checkout' )->customerEmailExists( $email )) {
					$data['result'] = 'exists';
				} else {
					$data['result'] = 'clean';
				}
			}
		}
	
		$this->getResponse()->setBody(Zend_Json::encode($data));
	}

    /**
     * Deleta endereço no checkout
     */
    public function deleteAddressCheckoutAction() {
        $addressId = $this->getRequest()->getParam( 'address_id' );
        $address = Mage::getModel('customer/address')->load($addressId);
        $address->delete();
    }
	
	/**
	 * See if supplied taxvat already exists
	 */
	public function taxvatExistsAction() {
		$taxvat = $this->getRequest()->getParam( 'taxvat' );
		$data = array('result' => 'clean');
	
		if ($taxvat && $taxvat != '') {
			if (Mage::helper( 'onestic_checkout' )->customerTaxvatExists( $taxvat )) {
				$data['result'] = 'exists';
			} else {
				$data['result'] = 'clean';
			}
		}
	
		$this->getResponse()->setBody(Zend_Json::encode($data));
	
	}
	
	/**
	 * Send new password to customer
	 */
	public function sendNewPasswordAction() {
		$result = new stdClass();
		$result->error = false;
	
		$email = $this->getRequest()->getPost( 'email' );
		if ( $email ) {
			if ( !Zend_Validate::is( $email, 'EmailAddress' ) ) {
				$result->error = true;
				$result->message = $this->__( 'Invalid email address.' );
				$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
				return;
			}
			$customer = Mage::getModel( 'customer/customer' )
			->setWebsiteId( Mage::app()->getStore()->getWebsiteId() )
			->loadByEmail( $email );
	
			if ( $customer->getId() ) {
				try {
					$newPassword = $customer->generatePassword();
					$customer->changePassword( $newPassword, false );
					$customer->sendPasswordReminderEmail();
	
					$result->title = $this->__( 'Your new password will arrive over email' );
					$result->message = $this->__( 'Please wait just a few minutes for an email to arrive from our store providing you your new password to login.' );
					$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
					return;
				} catch ( Exception $e ) {
	
				}
			} else {
				$result->error = true;
				$result->message = $this->__( 'This email address was not found in our records.' );
				$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
				return;
			}
		} else {
			$result->error = true;
			$result->message = $this->__( 'Please enter your email.' );
			$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
			return;
		}
	}
	
	/**
	 * Customer login via login form on checkout
	 */
	public function loginAction() {
		$result = new stdClass();
		$result->error = false;
	
		$username = $this->getRequest()->getPost( 'username', null );
		$password = $this->getRequest()->getPost( 'password', null );
	
		if ( $username && $password ) {
			try {
				Mage::getSingleton( 'customer/session' )->login( $username, $password );
			} catch ( Mage_Core_Exception $e ) {
				$title = '';
				switch ( $e->getCode() ) {
					case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
						$message = $this->__( 'This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper( 'customer' )->getEmailConfirmationUrl( $username ) );
						break;
					case Onestic_Checkout_Model_Customer::EXCEPTION_INVALID_PASSWORD:
						$title = $this->__( 'Sorry, that\'s the wrong password' );
						$message = $this->__( 'Please try again with another password or continue as a guest if you can\'t remember it.' );
						break;
					default:
						$message = $e->getMessage();
						break;
				}
				$result->error = true;
				$result->message = $message;
				$result->title = $title;
			} catch ( Exception $e ) {
				$result->error = true;
				$result->message = $e->getMessage();
			}
		} else {
			$result->error = true;
			$result->message = $this->__( 'Login and password are required' );
		}
	
		$this->getResponse()->setBody( Mage::helper( 'core' )->jsonEncode( $result ) );
	}

    public function addressAction() {
    	if ($this->getRequest()->getPost()) {
            $cep = $this->getRequest()->getPost('cep', false);
        } else {
            $cep = $this->getRequest()->getQuery('cep', false);
        }
        $cep = preg_replace('/[^\d]/', '', $cep);
        $soapArgs = array(
            'cep' => $cep,
            'encoding' => 'UTF-8',
            'exceptions' => 0
        );
        $return = '';
        try {
            $clientSoap = new SoapClient("https://apps.correios.com.br/SigepMasterJPA/AtendeClienteService/AtendeCliente?wsdl", array(
                'soap_version' => SOAP_1_1, 'encoding' => 'utf-8', 'trace' => true, 'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_BOTH, 'connection_timeout' => 5
            ));
            $result = $clientSoap->consultaCep($soapArgs);
            $dados = $result->return;
            if (is_soap_fault($result)) {
                $return = "var resultadoCEP = { 'uf' : '', 'cidade' : '', 'bairro' : '', 'tipo_logradouro' : '', 'logradouro' : '', 'resultado' : '0', 'resultado_txt' : 'cep nao encontrado' }";
            }else{
                $return = 'var resultadoCEP = { "uf" : "'.$dados->uf.'", "cidade" : "'.$dados->cidade.'", "bairro" : "'.$dados->bairro.'", "tipo_logradouro" : "", "logradouro" : "'.$dados->end.'", "resultado" : "1", "resultado_txt" : "sucesso%20-%20cep%20completo" }';
            }
        } catch (SoapFault $e) {
            $return = "var resultadoCEP = { 'uf' : '', 'cidade' : '', 'bairro' : '', 'tipo_logradouro' : '', 'logradouro' : '', 'resultado' : '0', 'resultado_txt' : 'cep nao encontrado' }";
        } catch (Exception $e) {
            $return = "var resultadoCEP = { 'uf' : '', 'cidade' : '', 'bairro' : '', 'tipo_logradouro' : '', 'logradouro' : '', 'resultado' : '0', 'resultado_txt' : 'cep nao encontrado' }";
        }
        echo $return;
    }

} 
