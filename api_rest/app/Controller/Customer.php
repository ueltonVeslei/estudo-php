<?php
class Controller_Customer extends Controller {

	// Recupera os dados da conta
	protected function _get() {
		$customerID = $this->getData('ID');
		if ($customerID) {
			$customer = Mage::getModel('customer/customer')->load($customerID);
			if ($customer->getId()) {
				$result = $customer->toArray();
				$this->setResponse('status',Standard::STATUS200);
				$this->setResponse('data',$result);
			} else {
				$this->setResponse('status',Standard::STATUS404);
				$this->setResponse('data','Cliente não encontrado');
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Cliente não informado');
		}
	}

	// Assinatura de Newsletter
	protected function _post() {
		if ($post = (array)$this->getData('body')) {
			$customer = Mage::getModel('customer/customer')->load($post['entity_id']);
			if ($customer->getId()) {
				$email = $customer->getEmail();
			} else {
				$email = $post['email'];
			}
			try {
				Mage::getModel('newsletter/subscriber')->subscribe($email);
				$this->setResponse('status',Standard::STATUS200);
				$this->setResponse('data','Assinatura realizada com sucesso');
			} catch(Exception $e) {
				$this->setResponse('status',Standard::STATUS500);
				$this->setResponse('data','Ocorreu um erro: ' . $e->getMessage());
			}
		} else {
			$this->setResponse('status',Standard::STATUS404);
			$this->setResponse('data','Dados não informados');
		}
	}

	// Atualiza os dados do cadastro
	protected function _put() {
		if ($post = (array)$this->getData('body')) {

			if ($_SERVER['HTTP_HOST'] == 'vcdelivery.com.br' || 
				$_SERVER['HTTP_HOST'] == 'www.vcdelivery.com.br') {

				$store_id = '23';
				Mage::app()->setCurrentStore($store_id);
				
			}	

			// se tiver o dado 'checkuser' ele vai apenas verificar se o email é existente na base de dados
			if (isset($post['checkuser'])) {
				$hasEmail = Mage::getModel('customer/customer')->setStore(Mage::app()->getStore());
				$hasEmail = $hasEmail->loadByEmail($post['email']);
				if ($hasEmail->getId()) {
					$this->setResponse('status',Standard::STATUS200);
					return $this->setResponse('data',['registered' => true, 'customer' => $hasEmail->toArray() ]);
				} else {
					$this->setResponse('status',Standard::STATUS500);
					return $this->setResponse('data',['registered' => false]);
				}
			}

			if (isset($post['editpassword'])) {
				$post['password_hash'] = Mage::helper('core')->getHash($post['password_hash'], Mage_Admin_Model_User::HASH_SALT_LENGTH);
			}


			$customer = Mage::getModel('customer/customer')->load($post['entity_id']);
			foreach ($post as $field => $value) {
				if (!in_array($field,$this->_excludes)) {
					$customer->setData($field,$value);
				}
			}
			try {
				$customer->save();
				$this->setResponse('status',Standard::STATUS200);
				$this->setResponse('data',$customer->toArray());
			} catch(Exception $e) {
				$this->setResponse('status',Standard::STATUS500);
				$this->setResponse('data','Ocorreu um erro: ' . $e->getMessage());
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

}
