<?php

/**
 * @category   RMO
 * @package    RMO_Integrator
 * @author     Renato Marcelino <renato@skyhub.com.br>
 * @company    SkyHub
 * @copyright (c) 2013, SkyHub
 * 
 * 
 * SkyHub: Especialista em integrações para e-commerce.
 * Integramos sua loja Magento com os principais Marketplaces
 * e ERPs do mercado nacional. 
 * Para mais informações acesse: www.skyhub.com.br
 */

class RMO_Integrator_Model_Catalog_Product_Api extends Mage_Catalog_Model_Product_Api {
   
    
   public function importCsv($csv, $shouldIndex) {
       $import = Mage::getModel('rmointegrator/importExport_import');
       return $import->importProducts($csv);
   } 
   
   
   public function urls($productSkus) {
     $collection = Mage::getModel("catalog/product")
             ->getCollection()
             ->addAttributeToFilter( 'sku', array( 'in' => $productSkus ) )
             ->addUrlRewrite();
     $result = array();
     foreach($collection as $prod) {
        $result[] = array( 'key' =>  $prod->getSku(), 'value' => $prod->getProductUrl());
     }
     return $result;
   }
   
   public function itemsPaged($storeCode = null, $curPage = null, $pageSize = null, $updated_from = null, $attribute_set = null, $getVariationPrices = null) {
       $collection = $this->_itemsCollection($storeCode, $updated_from, $attribute_set);
       
       if ($curPage && $pageSize) { 
        $collection->setPageSize($pageSize)->setCurPage($curPage);
       }
       
       
       
       if ($collection->getLastPageNumber() >= $curPage) {
        return $this->_addMediaGalleryToProducts($collection, $getVariationPrices);
       } else {
           return array();
       }
   }
   
   public function items($storeCode = null, $updated_from = null, $attribute_set = null) {
       $collection = $this->_itemsCollection($storeCode, $updated_from, $attribute_set);
       return $this->_addMediaGalleryToProducts($collection);
   }
   
   protected function _addMediaGalleryToProducts($productCollection, $getVariationPrices) {
       $result = array();
       
        foreach ($productCollection as $product) {
            Mage::helper("rmointegrator")->log('ProductId: ' . $product->getId() . " \n");
            
            $product->load('media_gallery');
            $product_data = $this->_toArray($product, $getVariationPrices);

            $exportConfigurableWithoutVariations = Mage::getStoreConfig('rmointegrator/catalog/export_configurable_without_variations');
            
            if ($exportConfigurableWithoutVariations || !$this->_configurableWithoutEnabledVariations($product_data)) {
              $result[] = $this->_toArray($product, $getVariationPrices);
            }
        }
        return $result;
   }
   
   protected function _configurableWithoutEnabledVariations($product_data) {
       return array_key_exists('configurable_attributes', $product_data) && 
               count($product_data['configurable_attributes']) > 0 && 
                count($product_data['variations']) == 0;
   }
    
    protected function _setCurrentStoreCode($storeCode = null){
        if($storeCode){
            $currentStoreCode = $storeCode;
            Mage::app()->setCurrentStore($storeCode);
        } else{
            $catalogWebstoreConfig = Mage::getStoreConfig('rmointegrator/catalog/catalog_webstore');
            if($catalogWebstoreConfig){
              $currentStoreCode = $catalogWebstoreConfig;
              Mage::app()->setCurrentStore($catalogWebstoreConfig);
            }
        }
        return $currentStoreCode;
    }
   
   protected function _itemsCollection($storeCode = null, $updated_from = null, $attribute_set = null) {
       $currentStoreCode = $this->_setCurrentStoreCode($storeCode);

       $visibilities = Mage::getStoreConfig('rmointegrator/catalog/visibilities_to_export');
       
       $collection = Mage::getModel("catalog/product")->getCollection()
                     ->addAttributeToFilter('visibility', array('in' => explode(',', $visibilities)))
                     ->addAttributeToSelect("*");
       
       if (!Mage::getStoreConfig('rmointegrator/catalog/import_disabled')) {
           $collection->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
       }
       

       if ($currentStoreCode) {
           $collection->addStoreFilter();
       }
       if ($attribute_set && strlen($attribute_set) > 0) { 
        $collection->addAttributeToFilter('attribute_set_id', $attribute_set);
       }
       if ($updated_from && strlen($updated_from) > 0) { 
        $collection->addAttributeToFilter('updated_at', array('gteq' => $updated_from));
       }
       $collection->addUrlRewrite();
       return $collection;
   }
   
   public function listCreated($curPage = null, $pageSize = null) {
        $productIntegratorCollection = $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::CREATED, 
                $curPage, $pageSize);
        $products = $this->_loadProductsByProductIntegrator($productIntegratorCollection);
        
        $result = array();
        foreach ($products as $product) {
            $result[] = $this->_toArray($product);
        }

        return $result;
    }
    
    function utf8_for_xml($string) {
        return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
    }
    
    /**
     * 
     * 
     * @param type $product
     * @return type
     */
    protected function _toArray($product, $getVariationPrices) {
        $multiselectAttributes = array();
        $attributes[] = array( 'key' => 'product_id', 'value' => $product->getId());
        $attributes[] = array( 'key' => 'sku', 'value' => $product->getSku());
        $attributes[] = array( 'key' => 'set', 'value'        => $product->getAttributeSetId());
        $attributes[] = array( 'key' => 'type', 'value'       => $product->getTypeId());
        $attributes[] = array( 'key' => 'categories', 'value' =>  $this->implodeIfArray($product->getCategoryIds()));
        $attributes[] = array( 'key' => 'websites', 'value'   => $this->implodeIfArray($product->getWebsiteIds()));
        
        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
            if ($this->_isAllowedAttribute($attribute)) {
                if($attribute->getFrontendInput() == "multiselect") {
                    $multiselectAttributes[] = Array ("key" => $attribute->getAttributeCode(), "value" => $this->implodeIfArray($product->getData($attribute->getAttributeCode())));
                } else {
                  $entry =  Array ("key" => $attribute->getAttributeCode(), "value" => $this->utf8_for_xml($this->implodeIfArray($product->getData($attribute->getAttributeCode()))));
                  if($entry['key'] == 'special_price' || ($entry['key'] == 'price' && $this->_isBundle($product))) {
                      $entry['value'] = Mage::helper("rmointegrator")->getFinalPrice($product);
                  }
                  $attributes[] = $entry;
                }
                
            }
        }
        
        $result['image_urls'] = array();
        foreach($product->getMediaGalleryImages() as $image) {
          if($product->getImage() && strpos($image->getUrl(), $product->getImage()) !== false) {
              array_unshift($result['image_urls'], $image->getUrl());
          } else {
            $result['image_urls'][] = $image->getUrl();
          }
        }
        
        $result['attributes'] = $attributes;
        if (count($multiselectAttributes) > 0 ) {
            $result['multiselect_attributes'] = $multiselectAttributes;
        }
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $result['configurable_attributes'] = Mage::helper("rmointegrator")->getConfigurableAttributes($product);
            $result["variations"] = Mage::helper("rmointegrator")->getConfigurableProductVariation($product, $getVariationPrices);
        } elseif ($this->_isBundle($product)) {
            $result["bundle_components"] = Mage::helper("rmointegrator")->getBundleComponents($product);
        }
        
        
        $result['full_url'] = $product->getProductUrl();
        
        return  $result;
    }

    protected function _isBundle($product){
      return $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE;
    }
    
    protected function _getFinalPrice($product) {
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
          return Mage::helper("rmointegrator")->getBundleFinalPrice($product);
        }
         $observer = new Varien_Event_Observer();
         $product->getFinalPrice();
         $event = new Varien_Event(array('product'=>$product, 'qty' => 1));
         $observer->setData(array('event'=> $event));
         Mage::getModel("catalogrule/observer")->processFrontFinalPrice($observer);
         return $product->getData("final_price");
    }
    
    public function implodeIfArray($data) {
        if (is_array($data)) {
           return implode(' ; ', $data);
        } else {
            return $data;
        }
    }
    
    public function listCreatedCount() {
        return $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::CREATED)->count();
    }
    
    public function confirmListCreatedReceived($receivedSkus) {
        $this->_deleteProductIntegratorsBySku($receivedSkus, RMO_Integrator_Model_Status::CREATED);
        return true;
    }
    
    public function listUpdated($curPage = null, $pageSize = null) {
        $productIntegratorCollection = $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::UPDATED, 
                $curPage, $pageSize);
        $products = $this->_loadProductsByProductIntegrator($productIntegratorCollection);
        
        $result = array();
        foreach ($products as $product) {
            $result[] = $this->_toArray($product);
        }

        return $result;
    }
    
    public function listUpdatedCount() {
        return $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::UPDATED)->count();
    }
    
    public function confirmListUpdatedReceived($receivedSkus) {
        $this->_deleteProductIntegratorsBySku($receivedSkus, RMO_Integrator_Model_Status::UPDATED);
        return true;
    }
    
    public function listDeleted($curPage = null , $pageSize = null) {
        $collection = $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::DELETED, 
                $curPage, $pageSize);
        $skusToReturn = array();
        foreach ($collection as $product_integrator) {
            if ($product_integrator->getProductSku()) {
                $skusToReturn[]= $product_integrator->getProductSku();
            }
        }
        return $skusToReturn;
    }
    
    public function listDeletedCount() {
        return $this->_getProductIntegratorCollection(RMO_Integrator_Model_Status::DELETED)->count();
    }
    
    public function confirmListDeletedReceived($receivedSkus) {
        $this->_deleteProductIntegratorsBySku($receivedSkus, RMO_Integrator_Model_Status::DELETED);
        return true;
    }
    
    protected function _getProductIntegratorCollection($status, $curPage = null, $pageSize = null) {
        $collection = Mage::getModel("rmointegrator/catalog_product_integrator")->getCollection()
                       ->addFieldToFilter('status', array('eq' => $status));
        if ($curPage && $pageSize) {
            $collection->setPageSize($pageSize)->setCurPage($curPage);
        }
        
        return $collection;
    }
    
    protected function _loadProductsByProductIntegrator($productIntegratorCollection) {
        $productsToReturn = array();
        foreach ($productIntegratorCollection as $product_integrator) {
           $id = Mage::getModel('catalog/product')->getIdBySku($product_integrator->getProductSku());
           $product = Mage::getModel("catalog/product")->load($id);
           if ( $product && $product->getId() && !$this->_shouldSkipProduct($product) ) {
              // $product->load('media_gallery');
               $productsToReturn[]= $product;
           }
        }
        return $productsToReturn;
    }
    
    protected function _shouldSkipProduct($product) {
        return Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE == $product->getVisibility();
    }
   
    protected function _deleteProductIntegratorsBySku($arrayOfSkus, $productIntegratorStatus) {
        foreach($arrayOfSkus as $sku) {
            $product_integrator = Mage::getModel("rmointegrator/catalog_product_integrator")->loadByProductSku($sku);
            if ($product_integrator->getId() && $product_integrator->getStatus() == $productIntegratorStatus) {
                $product_integrator->delete();
            }
        }
    }
}
