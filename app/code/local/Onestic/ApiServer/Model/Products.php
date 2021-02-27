<?php
class Onestic_ApiServer_Model_Products extends Mage_Core_Model_Abstract {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('onestic_apiserver/products');
	}
	
	public function populate($page=NULL) {
	    $api = Mage::getModel('onestic_apiserver/api_products');
	    $per_page = Mage::helper('onestic_apiserver')->getConfig('products_per_page');
	    $current_page = $page;
	    if (!$page) {
	    	$current_page = Mage::helper('onestic_apiserver')->getConfig('current_product_page');
	    }
    	if (!$current_page) $current_page = 1;
	    $products = $api->getCollection(array('per_page' => $per_page, 'page' => $current_page));
	    $count = $success = $errors = 0;
	    foreach ($products['body']->products as $product) {
	        try {
				$this->create($product);
				$success++;
	        } catch (Exception $e) {
	            Mage::log('ERRO PRODUCT POPULATE: ' . $e->getMessage(), null, 'onestic_apiserver.log');
	            $errors++;
	        }
	        $count++;
	    }
	    
	    if ($count == $per_page && !$page) { // ATUALIZA NÚMERO DA PÁGINA DE REGISTRO DOS PEDIDOS
    	    Mage::helper('onestic_apiserver')->updateConfig('current_product_page',$current_page+1);
	    }
	    
	    return array('total' => $products['body']->total,'success' => $success,'errors' => $errors,'count' => $count);
	}
	
	public function getTotal($conditions) {
		$api = Mage::getModel('onestic_apiserver/api_products');
		$orders = $api->getCollection($conditions);
		$total = 0;
		if ($orders['httpCode'] == 200) {
			$total = $orders['body']->total;
		}
	
		return $total;
	}
	
	public function checkExists($product) {
		$productMage = Mage::getModel("catalog/product")->loadByAttribute("sku", $product->sku);
		if ($productMage) {
			if (!$productMage->getId()) {
				Mage::getModel('onestic_apiserver/api_products')->remove($product->sku);
				$localProduct = Mage::getModel('onestic_apiserver/products')->load($product->sku, 'sku');
				if ($localProduct->getId()) {
					$localProduct->delete();
				}
				return false;
			}
		}
		
		return true;
	}
	
	public function create($product) {
		if ($this->checkExists($product)) {
			$productExists = Mage::getModel('onestic_apiserver/products')->load($product->sku, 'sku');
			if (!$productExists->getId()) {
			    $data = array(
			        'sku'				=> $product->sku,
			        'name'				=> $product->name,
			        'status'			=> $product->status,
			    	'updated_at'		=> date('Y-m-d H:i'),
			    	'qty'				=> $product->qty,
			    	'price'				=> $product->price,
			    	'promotional_price'	=> $product->promotional_price
			    );
			    if (isset($product->removed)) {
			    	$data['removed'] = ($product->removed == 'true') ? 'SIM' : 'NÃO';
			    }
			    $hasProduct = false;
			    $productMage = Mage::getModel("catalog/product")->loadByAttribute("sku", $product->sku);
			    if ($productMage) {
				    if ($productMage->getId()) {
				        $data['product_id'] = $productMage->getId();
				        $hasProduct = true;
				    }
			    }
			    $this->setData($data);
			    try {
			       $this->save();
			    } catch (Exception $e) {
			        Mage::log('ERRO PRODUCT POPULATE: ' . $e->getMessage(), null, 'onestic_apiserver.log');
			        Mage::throwException($e->getMessage());
			    }
			}
		}
	}
	
	public function update($data) {
	    $product = $this->load($data['sku'], 'sku');
	    if ($product->getId()) {
	    	$product->setData('name',$data['name']);
	    	$product->setData('qty',$data['qty']);
	    	$product->setData('price',$data['price']);
	    	$product->setData('promotional_price',$data['promotional_price']);
	    	$product->setData('status',$data['status']);
	    	$product->setData('status_sync','NÃO');
	    	$product->setData('removed',($data['status'] == 'enabled') ? 'NÃO' : 'SIM');
	    	$product->setData('update_at',date('Y-m-d H:i'));
	    	$product->save();
	    } else {
	    	$this->create((object)$data);
	    }
	}
	
	public function synced($sku) {
		$product = $this->load($sku, 'sku');
		if ($product->getId()) {
			$product->setData('status_sync','SIM');
			$product->setData('update_at',date('Y-m-d H:i'));
			$product->save();
		}
	}

}