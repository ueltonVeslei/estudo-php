<?php
class Onestic_Performa_Model_Queue extends Mage_Core_Model_Abstract {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('onestic_performa/queue');
	}
	
	public function populate($page=NULL) {
	    $per_page = 200;
	    $current_page = $page;
	    if (!$page) {
	    	$current_page = 1;
	    }
    	if (!$current_page) $current_page = 1;
	    $products = array('per_page' => $per_page, 'page' => $current_page);
	    $count = $success = $errors = 0;
	    foreach ($products['body']->products as $product) {
	        try {
				$this->create($product);
				$success++;
	        } catch (Exception $e) {
	            Mage::log('ERRO PRODUCT POPULATE: ' . $e->getMessage(), null, 'onestic_performa.log');
	            $errors++;
	        }
	        $count++;
	    }
	    
	    return array('total' => $products['body']->total,'success' => $success,'errors' => $errors,'count' => $count);
	}
	
	public function checkExists($product) {
		$productMage = Mage::getModel("catalog/product")->loadByAttribute("sku", $product->sku);
		if ($productMage) {
			if (!$productMage->getId()) {
				$localProduct = Mage::getModel('onestic_performa/queue')->load($product->sku, 'sku');
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
			$productExists = Mage::getModel('onestic_performa/queue')->load($product->sku, 'sku');
			if (!$productExists->getId()) {
			    $data = array(
			        'sku'				=> $product->sku,
			        'name'				=> $product->name,
			    	'updated_at'		=> date('Y-m-d H:i')
			    );
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
			        Mage::log('ERRO PRODUCT POPULATE: ' . $e->getMessage(), null, 'onestic_performa.log');
			        Mage::throwException($e->getMessage());
			    }
			}
		}
	}
	
	public function update($productId, $data) {
	    $product = $this->load($productId, 'product_id');
	    if ($product->getId()) {
	    	$product->setData('sku',$data['sku']);
	    	$product->setData('name',$data['name']);
	    	$product->setData('updated_at',date('Y-m-d H:i'));
	    	$product->save();
	    } else {
	    	$this->create((object)$data);
	    }
	}
	
	public function synced($productId) {
		$product = $this->load($productId, 'product_id');
		if ($product->getId()) {
			$product->setData('updated_at',date('Y-m-d H:i'));
			$product->save();
		}
	}

}