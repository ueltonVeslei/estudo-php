<?php
class Onestic_ApiServer_Model_Products_Updater extends Varien_Object {

    private $_excludeAttributes = array(
        'sku','name','description','status','price','cost','weight','volume_altura','volume_comprimento',
        'volume_largura','manufacturer','codigo_barras','visibility','created_at','update_at','type_id',
        'entity_type_id','attribute_set_id','entity_id','short_description','old_id','news_from_date',
        'news_to_date','url_key','url_path','country_of_manufacture','category_ids','required_options',
        'has_options','image_label','small_image_label','thumbnail_label','image','small_image','thumbnail',
        'media_gallery','msrp_enabled','msrp_display_actual_price_type','enable_googlecheckout','tax_class_id',
        'gallery','custom_design','custom_design_from','custom_design_to','custom_layout_update','page_layout',
        'options_container','gift_message_available','msrp','is_recurring','recurring_profile','group_price',
        'tier_price','updated_at','apiserver_send','description_standout','video'
    );
    
    private $_qtyRegs = 50;
    
    public function populate() {
	    Mage::getModel('onestic_apiserver/products')->populate();
	}
	
	public function products() {
	    if (Mage::helper('onestic_apiserver')->getConfig('cron_products')) {
    	    $helper = Mage::helper('onestic_apiserver');
    	    $products = Mage::getModel('onestic_apiserver/products')->getCollection()
    	                   ->addFieldToFilter('status_sync', array('like' => 'NÃO'))
    	                   ->setPageSize($this->_qtyRegs)
    	                   ->setCurPage(1);
    
            foreach ($products as $prd) {
                $this->_send($prd->getProductId()); 
            }
	    }
	}
	
	public function export($page=NULL) {
		$helper = Mage::helper('onestic_apiserver');
		$per_page = Mage::helper('onestic_apiserver')->getConfig('products_per_page');
		$current_page = $page;
		if (!$page) {
			$current_page = Mage::helper('onestic_apiserver')->getConfig('current_product_page');
		}
		if (!$current_page) $current_page = 1;
		$products = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect("*")
			->addAttributeToFilter('visibility', array('in' => array(2,4)))
			->addAttributeToFilter('type_id',array('in' => array('configurable','simple')));
		$total = $products->getSize();
		$products->setPageSize($per_page)
		->setCurPage($current_page);
		$count = $success = $errors = 0;
		foreach ($products as $prd) {
			try {
				$this->_send($prd->getId());
				$success++;
			} catch (Exception $e) {
				Mage::log('ERRO PRODUCT LOCAL POPULATE: ' . $e->getMessage(), null, 'onestic_apiserver.log');
				$errors++;
			}
			$count++;
		}
			
		if ($count == $per_page && !$page) { // ATUALIZA NÚMERO DA PÁGINA DE REGISTRO DOS PEDIDOS
			Mage::helper('onestic_apiserver')->updateConfig('current_product_page',$current_page+1);
		}
	
		return array('total' => $total,'success' => $success,'errors' => $errors,'count' => $count);
	}
	
	public function sendSelection($products) {
	    $errors = $success = 0;
	    foreach ($products as $prd) {
	        $retorno = $this->_send($prd);
	        if ($retorno) {
	            $success++;
	        } else  {
	            $errors++;
	        }
	    }
	    return array('errors' => $errors, 'success' => $success);
	}
	
	protected function _remove($product) {
		$control = Mage::getModel('onestic_apiserver/products')->load($product->getId(), 'product_id');
		if ($control->getId()) {
			$api = Mage::getModel('onestic_apiserver/api_products');
			Mage::log('REMOVE PRODUCT :: ' . $control->getSku(),null,'onestic_apiserver.log');
			$remove = $api->remove($control->getSku());
			if (isset($remove['body']->error)) { // Produto não existe
				Mage::log('PRODUCT REMOVE ERROR :: ' . $control->getSku() . ': ' . var_export($remove, true),null,'apiserver_sync_prd.log');
				$productData = $this->_getProductData($product);
				$productData['status'] = 'disabled';
				$productData['qty'] = 0;
				
				$update = $api->update($product->getSku(), array('product'=>$productData));
				$control->setData('status_sync','SIM');
			}
			
			if (in_array($remove['httpCode'], array(200,404))) {
				$control->setData('removed','SIM');
			}
			
			$control->setData('update_at',date('Y-m-d H:i'));
			$control->save();
		} else {
			Mage::log('REMOVE PRODUCT :: NO CONTROL FOR ' . $product->getId(),null,'onestic_apiserver.log');
		}
	}
	
	protected function _send($productId) {
		$control = Mage::getModel('onestic_apiserver/products')->load($productId, 'product_id');
		$product = Mage::getModel('catalog/product')->load($productId);
		if (!$control->getId()) {
			Mage::log('PRODUCT SEND :: NOT EXISTS ' . $productId,null,'onestic_apiserver_prd.log');
			if ($product->getId()) {
				$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
				$prd = new stdClass();
				$prd->sku = $product->getSku();
			    $prd->name = $product->getName();
			    $prd->status = (($product->getStatus() == 1) ? "enabled" : "disabled");
			    $prd->qty = $stock->getQty();
			    $prd->price = $product->getPrice();
			    $prd->promotional_price = $product->getFinalPrice();
			    Mage::getModel('onestic_apiserver/products')->create($prd);
				$control = Mage::getModel('onestic_apiserver/products')->load($productId, 'product_id');
			}
		}
			
		$control->setData('status_sync','NÃO');
		$control->setData('update_at',date('Y-m-d H:i'));
		$control->save();
		
	    $api = Mage::getModel('onestic_apiserver/api_products');
	    
        $remove = false;
        if(!$product) {
        	$remove = true;
        } elseif (!$product->getId()) {
	        $remove = true;
        }
        
        if ($remove) {
        	// Produto não existe mais no catálogo, excluir da ApiServer
			$this->_remove($product);        	
        	return false;
        }

        $productData = $this->_getProductData($product);
        
        if (!$productData) {
        	$this->_remove($product);
        	return false;
        }
		
        Mage::getModel('onestic_apiserver/products')->update($productData);
        $check = $api->getProduct($product->getSku());
        if (isset($check['body']->error)) { // Produto não existe
            $retorno = $api->create(array('product'=>$productData));
        } else { // Produto existe
            $retorno = $api->update($product->getSku(), array('product'=>$productData));
        }

        if (isset($retorno['body']->error)) { // Produto não existe
            Mage::log('PRODUCT SEND :: ERROR >> ' . $product->getSku() . ' >> ' . var_export($retorno, true),null,'onestic_apiserver_prd.log');
            return false;
        }
        Mage::getModel('onestic_apiserver/products')->synced($product->getSku());
        
        return true;
	}
	
	protected function _getProductData($product) {
		$categories = $this->_getCategories($product);
		
		if (!$categories) {
			// Produto sem categorias permitidas, remover da ApiServer
			Mage::log('PRODUCT SEND :: ERROR >> NO CATEGORY FOR ' . $product->getSku(),null,'apiserver_sync_prd.log');
			return false;
		}
		
		$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
		$productData = array(
				'sku'                   => $product->getSku(),
				'name'                  => $product->getName(),
				'description'           => $product->getDescription(),
				'status'                => (($product->getStatus() == 1) ? "enabled" : "disabled"),
				'qty'                   => $stock->getQty(),
				'price'                 => $product->getPrice(),
				'promotional_price'     => $product->getFinalPrice(),
				'cost'                  => $product->getCost(),
				'weight'                => $product->getWeight(),
				'height'                => $product->getVolumeAltura(),
				'width'                 => $product->getVolumeComprimento(),
				'length'                => $product->getVolumeLargura(),
				'brand'                 => $product->getAttributeText('manufacturer'),
				'ean'                   => $product->getCodigoBarras(),
				'categories'            => $categories,
				'images'                => $this->_getImages($product),
				'specifications'        => $this->_getSpecifications($product)
		);
		
		if ($product->getTypeId() == 'configurable') {
			$variations = $this->_getVariations($product);
			$productData['variation_attributes'] = $variations['attributes'];
			$productData['variations'] = $variations['variations'];
			$productData['qty'] = $variations['qtyTotal'];
			$productData['weight'] = $variations['weight'];
		}
		
		return $productData;
	}
	
	protected function _getCategories($product) {
	    $categories = array();
	    $api = Mage::getModel('onestic_apiserver/api_categories');
	    $apiCategories = $api->categories();
	    $allowedCats = explode(',', Mage::helper('onestic_apiserver')->getConfig('categories'));
	    $notAllowed = array();
	    foreach ($product->getCategoryIds() as $cat) {
	        $category = Mage::getModel('catalog/category')->load($cat);
	        if (!in_array($cat,$apiCategories) && in_array($cat,$allowedCats)) {
	            $api->create(array(
	                'category' => array(
        	            'code' => $category->getId(),
        	            'name' => $category->getName()
	                )
    	        ));
	        }
	        if (in_array($cat,$allowedCats)) {
    	        $categories[] = array(
                    'code' => $category->getId(),
    	            'name' => $category->getName()
    	        );
            } else {
            	$notAllowed[] = $category->getName();
            }
	    }
	    
	    return $categories;
	}
	
	protected function _exclude($product) {
	    $excludes = explode(',', Mage::helper('')->getConfig('excludes'));
	    $exclude = false;
	    foreach ($product->getCategoryIds() as $cat) {
            if (in_array($cat,$excludes)) {
                $exclude = true;
                break;
            }
	    }
	     
	    return $exclude;
	}
	
	protected function _getImages($product) {
	    $images = array();
	    foreach ($product->getMediaGalleryImages() as $image) {
	       $images[] = $image->getUrl();
	    }
	    
	    return $images;
	}
	
	protected function _getSpecifications($product) {
	    $attributes = $product->getAttributes();
	    $specs = array();
	    foreach ($attributes as $attribute) {
	        $code = $attribute->getAttributeCode();
	        if (!in_array($code, $this->_excludeAttributes)) {
	            $value = $attribute->getFrontend()->getValue($product);
	            if ($value) {
    	            $specs[] = array(
    	                "key"   => $code,
    	                "value" => $value
    	            );
	            }
	        }
	    }
	    
	    return $specs;
	}
	
	protected function _getVariations($product) {
	    $variation_attributes = array();
	    $superAttributes = $product->getTypeInstance(true)->getUsedProductAttributeIds($product);
	    foreach ($superAttributes as $sa) {
	        $attrib = Mage::getModel('eav/entity_attribute')->load($sa);
	        $variation_attributes[] = $attrib->getAttributeCode();
	    }
	    
	    $variationProducts = $product->getTypeInstance(true)->getUsedProductIds($product);
	    $variationsData = array();
	    $weight = 0;
	    $qty = 0;
	    foreach ($variationProducts as $variation) {
	        $pv = Mage::getModel('catalog/product')->load($variation);
	        $vStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($pv);
	        $varData = array(
	            'sku'               => $pv->getSku(),
	            'qty'               => $vStock->getQty(),
	            'ean'               => $pv->getCodigoBarras(),
	            'images'            => $this->_getImages($pv),
	            'specifications'    => $this->_getSpecifications($pv)
	        );
	        if ($weight < $pv->getWeight())
	            $weight = $pv->getWeight();
	        
	        $qty += $vStock->getQty(); 
	        
	        $variationsData[] = $varData;
	    }
	    
	    return array('variations' => $variationsData, 'attributes' => $variation_attributes, 'qtyTotal' => $qty, 'weight' => $weight);
	}
	
	public function sync($productId) {
		return $this->_send($productId);
	}
	
}