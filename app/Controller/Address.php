<?php
class Controller_Address extends Controller {

	protected $_excludes = array('customer_id', 'entity_id', 'parent_id');

	// Recuperar os endereços
	protected function _get() {
		$customerID = $this->getData('ID');
		if ($customerID) {
			$customer = Mage::getModel('customer/customer')->load($customerID);
			if ($customer->getId()) {
				$result = [];
				foreach($customer->getAddresses() as $address) {
					$result[] = $address->toArray();
				}
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

	// Incluir novo endereço
	protected function _post() {
		if ($post = (array)$this->getData('body')) {
			$address = Mage::getModel('customer/address');
			$address->setCustomerId($post['customer_id']);
			foreach ($post as $field => $value) {
				if (!in_array($field,$this->_excludes)) {
					$address->setData($field,$value);
				}
			}
			try {
				$address->save();
				$customer = Mage::getModel('customer/customer')->load($post['customer_id']);

				if ($customer->getId()) {
					$result = [];
					foreach($customer->getAddresses() as $address) {
						$result[] = $address->toArray();
					}
					$this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data',$result);
				} else {
					$this->setResponse('status',Standard::STATUS404);
					$this->setResponse('data','Cliente não encontrado');
				}
			} catch(Exception $e) {
				$this->setResponse('status',Standard::STATUS500);
				$this->setResponse('data','Ocorreu um erro: ' . $e->getMessage());
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

	// Atualizar endereço
	protected function _put() {
		if ($post = (array)$this->getData('body')) {
			$address = Mage::getModel('customer/address')->load($post['entity_id']);
			if ($address->getId()) {
				foreach ($post as $field => $value) {
					if (!in_array($field,$this->_excludes)) {
						$address->setData($field,$value);
					}
				}
				try {
					$address->save();
					$this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data','Endereço atualizado com sucesso');
				} catch(Exception $e) {
					$this->setResponse('status',Standard::STATUS500);
					$this->setResponse('data','Ocorreu um erro: ' . $e->getMessage());
				}
			} else {
				$this->setResponse('status',Standard::STATUS404);
				$this->setResponse('data','Endereço não encontrado');
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Dados não enviados');
		}
	}

	// Excluir endereço
	protected function _delete() {
		$addressID = $this->getData('ID');
		if ($addressID) {
			$address = Mage::getModel('customer/address')->load($addressID);
			$customer = Mage::getModel('customer/customer')->load($address->getCustomerId());
			if ($address->getId()) {
				try {
					$address->delete();

					if ($customer->getId()) {
						$result = [];
						foreach($customer->getAddresses() as $address) {
							$result[] = $address->toArray();
						}
						$this->setResponse('status',Standard::STATUS200);
						$this->setResponse('data',$result);
					} else {
						$this->setResponse('status',Standard::STATUS404);
						$this->setResponse('data','Cliente não encontrado');
					}
				} catch(Exception $e) {
					$this->setResponse('status',Standard::STATUS200);
					$this->setResponse('data','Ocorreu um erro: ' . $e->getMessage());
				}
			} else {
				$this->setResponse('status',Standard::STATUS404);
				$this->setResponse('data','Endereço não encontrado');
			}
		} else {
			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data','Endereço não informado');
		}
	}

}