<?php
class Controller_Crossover extends Controller {

	private $regions = [
		'AC' => 485,
		'AL' => 486,
		'AP' => 487,
		'AM' => 488,
		'BA' => 489,
		'CE' => 490,
		'DF' => 511,
		'ES' => 491,
		'GO' => 492,
		'MA' => 493,
		'MT' => 494,
		'MS' => 495,
		'MG' => 496,
		'PA' => 497,
		'PB' => 498,
		'PR' => 499,
		'PE' => 500,
		'PI' => 501,
		'RJ' => 502,
		'RN' => 503,
		'RS' => 504,
		'RO' => 505,
		'RR' => 506,
		'SC' => 507,
		'SP' => 508,
		'SE' => 509,
		'TO' => 510
	];

	private function generateCustomerData($post, $customer)
	{
		$cropname = strpos($post['client']['name'], ' ') !== false ? explode(' ', $post['client']['name']): [$post['client']['name']];
		$firstname = $cropname[0];
		unset($cropname[0]);
		$lastname = count($cropname) > 0 ? implode(' ', $cropname) : '';

		$dataCustomer = [
			'email' => $post['client']['mail'],
			'firstname' => $firstname,
			'group_id' => 1,
			'lastname' => $lastname,
			'taxvat' => $post['client']['cpf'],
			'tipopessoa' => 'Física'
		];

		foreach ($dataCustomer as $field => $value) {
			$customer->setData($field,$value);
		}

		return $customer;
	}

	private function generateAddressData($post, $customer)
	{

		$cropname = strpos($post['client']['name'], ' ') !== false ? explode(' ', $post['client']['name']): [$post['client']['name']];
		$firstname = $cropname[0];
		unset($cropname[0]);
		$lastname = count($cropname) > 0 ? implode(' ', $cropname) : '';

		$address = Mage::getModel('customer/address')->load(array_keys($customer->getAddresses())[0]);
		$address->setCustomerId($customer->getId());
		$dataAddress = [
			'firstname' => $firstname,
			'country_id' => 'BR',
			'postcode' => str_replace('-', '', $post['address']['zip']),
			'lastname' => $lastname,
			'city' => $post['address']['city'],
			'telephone' => $post['client']['cellphone'],
			'street' => [
				$post['address']['public_place'],
				$post['address']['complement'],
				$post['address']['number'],
				$post['address']['neighborhood']
			],
			'region_id' => isset($this->regions[$post['address']['uf']]) ? $this->regions[$post['address']['uf']] : 0
		];

		foreach ($dataAddress as $field => $value) {
			if (!in_array($field,$this->_excludes)) {
				$address->setData($field,$value);
			}
		}

		return $address;
	}

	protected function _post()
	{

		$storeID = $this->getData('STORE');

		settype($storeID, 'string');

		if ($post = (array)$this->getData('body')) {
			
			Mage::app()->setCurrentStore($storeID);
			
			$customer = Mage::getModel('customer/customer')->setStore(Mage::app()->getStore());
			$post['client'] = json_decode(json_encode($post['client']), true);
			$post['address'] = json_decode(json_encode($post['address']), true);
			$customer = $customer->loadByEmail($post['client']['mail']);

			$customer = $this->generateCustomerData($post, $customer);
			$customer->save();
			$customer = $customer->loadByEmail($post['client']['mail']);

			$address = $this->generateAddressData($post, $customer);
			$address->save();

			$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
			$customer->load(trim($id));
			Mage::getSingleton('customer/session')->loginById($customer->getId());

			$userSession = Mage::getSingleton('customer/session');
			$userSession->setCustomerAsLoggedIn($customer);

			Mage::dispatchEvent('customer_login', array('customer'=>$customer));

			$url = strpos(Mage::getUrl('customer/account/login'), '?') !== false ? explode('?', Mage::getUrl('customer/account/login'))[0] : Mage::getUrl('customer/account/login');

			$url = $url.'?uid='.$customer->getId().'&mid='.md5(time());
			$url = $post['url'] ? $url.'&redirect='.$post['url'] : $url;

			$this->setResponse('status',Standard::STATUS200);

			if ($customer->getId()) {
				return $this->setResponse('data', ['registered' => true, 'login_url' => $url]);
			}

			return $this->setResponse('data',['registered' => false, 'login_url' => $url]);

		} else {

			$this->setResponse('status',Standard::STATUS500);
			$this->setResponse('data',['error' => 'Dados do cliente e seu endereço não foram enviados.']);

		}
		
	}


}
