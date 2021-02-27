<?php
/**
 * @category   RMO
 * @package    RMO_Integrator
 * @author     Renato Marcelino <renato@skyhub.com.br>
 * @company    SkyHub
 * @copyright (c) 2013, SkyHub
 * 
 * 
 * SkyHub: Especialista em integrações para e-commerce
 * Integramos sua loja Magento com os principais Marketplaces
 * e ERPs do mercado nacional. 
 * Para mais informações acesse: www.skyhub.com.br
 * 
 */
class RMO_Integrator_Helper_Data extends Mage_Core_Helper_Abstract {
    
    
    public function log($string) {
        if (Mage::helper('rmointegrator')->debugMode()) {
            Mage::log($string, null, "SkyHub.log");
        } 
    }
    
    public function debugMode(){
        return Mage::getStoreConfig('rmointegrator/geral/debug_mode') == true;
    }
    
    public function str_getcsv_toAssociativeArray($csv_string) {
        Mage::helper('rmointegrator')->log("str_getcsv_toAssociativeArray INICIO ---X---");
        $data = str_getcsv($csv_string, "\n");
        Mage::helper('rmointegrator')->log("data: " . json_encode($data));
        $result = null;
        $keys = Array();
        $first_row = true;
        foreach($data as $row) {
            if($first_row) {
                $keys = str_getcsv($row, ","); 
                $first_row = false;
            } else {
                $result[]= array_combine($keys, str_getcsv($row, ","));
            }
        }
        Mage::helper('rmointegrator')->log("result: " . json_encode($result));
        Mage::helper('rmointegrator')->log("str_getcsv_toAssociativeArray FIM ---X---");
        return $result;
    }
            
    public function fgetcsv_toAssociativeArray($csv_string) {
        Mage::helper('rmointegrator')->log("--- fgetcsv_toAssociativeArray");
        
        $fiveMBs = 5 * 1024 * 1024;
        Mage::helper('rmointegrator')->log("fiveMBs: " . $fiveMBs );
        $fp = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
        fputs($fp, $csv_string);
        rewind($fp);
        
        $keys = fgetcsv($fp);
        Mage::helper('rmointegrator')->log("keys: " . json_encode($keys) );
        $csv = Array();
        while (!feof($fp)) {
            $entry = fgetcsv($fp);
            Mage::helper('rmointegrator')->log("entry: " . json_encode($entry) );
            $csv[] = array_combine($keys, $entry);
        }
        
        Mage::helper('rmointegrator')->log("result: " . json_encode($csv));
        Mage::helper('rmointegrator')->log("--- fgetcsv_toAssociativeArray END");
        return $csv;
    }
    
    public function toAssociativeArray($csv_string) {        
        return $this->fgetcsv_toAssociativeArray($csv_string);
        //$this->str_getcsv_toAssociativeArray($csv_string);
    }
            
    public function getBundleComponents($bundleProduct) {
        $optionCollection = $bundleProduct->getTypeInstance()->getOptionsCollection();
        $selectionCollection = $bundleProduct->getTypeInstance()->getSelectionsCollection($bundleProduct->getTypeInstance()->getOptionsIds());
        $options = $optionCollection->appendSelections($selectionCollection);
        $components = Array();
        foreach ($options as $option) {
            $selection = $this->getUniqueSelectionFromOption($option);
            if($selection){
                $component = Array();
                $component['associated_product_sku'] = $selection->getSku();
                $component['associated_product_qty'] = $selection->getSelectionQty();
                $components[] = $component;
            }
        }
        return $components;
    }
    public function getUniqueSelectionFromOption($option){
        $selections = $option->getSelections();
        if (sizeof($selections) == 1){
                $uniqueSelection = $selections[0];
        } else {
            foreach($selections as $selection){
                if($selection->getIsDefault){
                    $uniqueSelection = $selection;
                    break;
                }
            }
            if(!$uniqueSelection && sizeof($selections) > 0){
                $uniqueSelection = $selections[0];
            }
        }
        return $uniqueSelection;
    }

    public function getBundleFinalPrice($product){
        $optionsCollection = $product->getTypeInstance()->getOptionsCollection();
        $selectionsCollection = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds());
        $options = $optionsCollection->appendSelections($selectionsCollection);
        $priceModel = $product->getPriceModel();
        $price = 0;
        foreach ($options as $option) {
            $selection = $this->getUniqueSelectionFromOption($option);
            if($selection){
                $price += $priceModel->getSelectionPreFinalPrice($product, $selection, $selection->getSelectionQty());
            }
        }
        return $price;
    }
    
    /**
     * Returns all attributes that make up the $configurableProduct
     * 
     * It returns a bi-dimensional array of the form:
     *   $result = { { "attribute_name" => "...", attribute_code" => "..." }, 
     *               { "attribute_name" => "...", attribute_code" => "..." }, 
     *               ...} 
     * 
     * If the procuts passsed as argument is not a configurable product, the function will return the boolean false
     * 
     * @param type $configurableProduct
     * @return boolean
     */
    public function getConfigurableAttributes($configurableProduct) {
        if (!$configurableProduct || $configurableProduct->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return false;
        }
        $configurableAttributes = $configurableProduct->getTypeInstance()->getConfigurableAttributes();
        $result = array();
        foreach($configurableAttributes as $attribute) {
            $resultItem['attribute_name'] = $attribute->getProductAttribute()->getFrontend()->getLabel();
            $resultItem['attribute_code'] = $attribute->getProductAttribute()->getAttributeCode();
            $result[] = $resultItem;
        }
        
        return $result;
    }
    
    /**
     * Returns the ids of the chield products of the configurable product
     * 
     * If the product passed as argument is not a configurable product, the funciton returns the boolean false.
     * 
     * @param type $configurableProduct
     * @return boolean
     */
    public function getConfigurableAssociatedProducts($configurableProduct) {
        if (!$configurableProduct || $configurableProduct->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return false;
        }
        
        $array = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($configurableProduct->getId());
        return array_values($array[0]); 
        
    }
    
    public function getConfigurableProductVariation($configurableProduct, $getVariationPrices) {
        if (!$this->isConfigurableProduct($configurableProduct)) {
            return false;
        }
        $array = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($configurableProduct->getId());
        $associatedIds = array_values($array[0]); 
        $configurableAttributes = $this->getConfigurableAttributes($configurableProduct);
		$basePrice = $configurableProduct->getData("price");
		if($getVariationPrices == 'configurable_based_price'){
			$pricesByAttributeValues = $this->_getPricesByAttributeValues($configurableProduct, $basePrice);
		}
        $result = Array();   
        foreach ($associatedIds as $productId) {
            $product = Mage::getModel("catalog/product")->load($productId);
            if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                continue;
            }
            $variation = Array();
            $variation['associated_product_id']  = $productId;
            $variation['associated_product_sku'] = $product->getSku();
            $variation['weight'] = $product->getweight();
            foreach($product->getMediaGalleryImages() as $image) {
               $variation['image_urls'][] = $image->getUrl();
             }
			$totalPrice = $basePrice;
            $variation_attributes = Array();
            foreach ($configurableAttributes as $attributeData) {
				$value = $this->implodeIfArray($product->getData($attributeData['attribute_code']));
                $variation_attributes[] = Array("key" => $attributeData["attribute_code"], "value" => $value);
				if($getVariationPrices == 'configurable_based_price' && isset($pricesByAttributeValues[$value])){
					$totalPrice += $pricesByAttributeValues[$value];
				}
            }
            if ($getVariationPrices == 'configurable_based_price'){
				$variation['price'] = $totalPrice;
				$promotionalPrice = Mage::getModel('catalogrule/rule')->calcProductPriceRule($configurableProduct, $totalPrice);
				if(!$promotionalPrice){
					$promotionalPrice = $totalPrice;
				}
                $variation['special_price'] = $promotionalPrice;
            }elseif ($getVariationPrices == 'simple_product_price'){
                $variation['price'] = $product->getData("price");
                $variation['special_price'] = $this->getFinalPrice($product);
			}
            
            $length_att_code = Mage::getStoreConfig('rmointegrator/catalog/length_attribute_code');
            if ($length_att_code && $product->getData($length_att_code) ) {
               $variation_attributes[] = Array("key" => $length_att_code, "value" => $product->getData($length_att_code));
            }
            
            $height_att_code = Mage::getStoreConfig('rmointegrator/catalog/height_attribute_code');
            if ($height_att_code && $product->getData($height_att_code) ) {
               $variation_attributes[] = Array("key" => $height_att_code, "value" => $product->getData($height_att_code));
            }

            $width_att_code = Mage::getStoreConfig('rmointegrator/catalog/width_attribute_code');
            if ($width_att_code && $product->getData($width_att_code) ) {
               $variation_attributes[] = Array("key" => $width_att_code, "value" => $product->getData($width_att_code));
            }

            $ean_att_code = Mage::getStoreConfig('rmointegrator/catalog/ean_attribute_code');
            if ($ean_att_code && $product->getData($ean_att_code) ) {
               $variation_attributes[] = Array("key" => $ean_att_code, "value" => $product->getData($ean_att_code));
            }
            
            $ncm_att_code = Mage::getStoreConfig('rmointegrator/catalog/ncm_attribute_code');
            if ($ncm_att_code && $product->getData($ncm_att_code) ) {
               $variation_attributes[] = Array("key" => $ncm_att_code, "value" => $product->getData($ncm_att_code));
            }

            $variation["attributes"] = $variation_attributes;
            $result[] = $variation;
        }

        return $result;
    }
	
	public function _getPricesByAttributeValues($configurableProduct, $basePrice) {
		$pricesByAttributeValues = array();
		Mage::helper('rmointegrator')->log("basePrice: $basePrice");
		$configurableAttributes = $configurableProduct->getTypeInstance(true)->getConfigurableAttributes($configurableProduct);
		foreach ($configurableAttributes as $attributeData) {
			$prices = $attributeData->getPrices();
			foreach($prices as $price){
				if ($price['is_percent']){ //if the price is specified in percents
					$pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'] * $basePrice / 100;
					$priceKey = $price['value_index'];
					$priceValue = $price['pricing_value'];
					Mage::helper('rmointegrator')->log("priceKey: $priceKey, priceValue: $priceValue");
				}
				else { //if the price is absolute value
					$pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'];
					$priceKey = $price['value_index'];
					$priceValue = $price['pricing_value'];
					Mage::helper('rmointegrator')->log("priceKey: $priceKey, priceValue: $priceValue");
				}
			}
		}
		return $pricesByAttributeValues;
	}

    public function getFinalPrice($product) {
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
    
    
    protected function isConfigurableProduct($configurableProduct) {
        return $configurableProduct && $configurableProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
    }
    
   public function implodeIfArray($data) {
        if (is_array($data)) {
           return implode(' ; ', $data);
        } else {
            return $data;
        }
    }
    
    
    public function associateProducts(Mage_Catalog_Model_Product $product, $simpleSkus, $priceChanges = array(), $configurableAttributes = array()) {
        if (empty($simpleSkus)) {
            return $this;
        }
        
        $newProductIds = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('sku', array('in' => (array) $simpleSkus))
            ->addFieldToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->getAllIds();

        $oldProductIds = Mage::getModel('catalog/product_type_configurable')->setProduct($product)->getUsedProductCollection()
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions()
            ->getAllIds();

        $usedProductIds = array_diff($newProductIds, $oldProductIds);

        if (!empty($usedProductIds)) {
            if ($product->isConfigurable()) {
                $this->_initConfigurableAttributesData($product, $usedProductIds, $priceChanges, $configurableAttributes);
            } elseif ($product->isGrouped()) {
                $relations = array_fill_keys($usedProductIds, array('qty' => 0, 'position' => 0));
                $product->setGroupedLinkData($relations);
            }
        }
        
        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $mainProduct
     * @param array $simpleProductIds
     * @param array $priceChanges
     * @return Bubble_Api_Helper_Catalog_Product
     */
    protected function _initConfigurableAttributesData(Mage_Catalog_Model_Product $mainProduct, $simpleProductIds, $priceChanges = array(), $configurableAttributes = array())
    {
        if (!$mainProduct->isConfigurable() || empty($simpleProductIds)) {
            return $this;
        }

        $mainProduct->setConfigurableProductsData(array_flip($simpleProductIds));
        $productType = $mainProduct->getTypeInstance(true);
        $productType->setProduct($mainProduct);
        $attributesData = $productType->getConfigurableAttributesAsArray();

        if (empty($attributesData)) {
            // Auto generation if configurable product has no attribute
            $attributeIds = array();
            foreach ($productType->getSetAttributes() as $attribute) {
                if ($productType->canUseAttribute($attribute)) {
                    $attributeIds[] = $attribute->getAttributeId();
                }
            }
            $productType->setUsedProductAttributeIds($attributeIds);
            $attributesData = $productType->getConfigurableAttributesAsArray();
        }
        if (!empty($configurableAttributes)){
            foreach ($attributesData as $idx => $val) {
                if (!in_array($val['attribute_id'], $configurableAttributes)) {
                    unset($attributesData[$idx]);
                }
            }
        }

        $products = Mage::getModel('catalog/product')->getCollection()
            ->addIdFilter($simpleProductIds);

        if (count($products)) {
            foreach ($attributesData as &$attribute) {
                $attribute['label'] = $attribute['frontend_label'];
                $attributeCode = $attribute['attribute_code'];
                foreach ($products as $product) {
                    $product->load($product->getId());
                    $optionId = $product->getData($attributeCode);
                    $isPercent = 0;
                    $priceChange = 0;
                    if (!empty($priceChanges) && isset($priceChanges[$attributeCode])) {
                        $optionText = $product->getResource()
                            ->getAttribute($attribute['attribute_code'])
                            ->getSource()
                            ->getOptionText($optionId);
                        if (isset($priceChanges[$attributeCode][$optionText])) {
                            if (false !== strpos($priceChanges[$attributeCode][$optionText], '%')) {
                                $isPercent = 1;
                            }
                            $priceChange = preg_replace('/[^0-9\.,-]/', '', $priceChanges[$attributeCode][$optionText]);
                            $priceChange = (float) str_replace(',', '.', $priceChange);
                        }
                    }
                    $attribute['values'][$optionId] = array(
                        'value_index' => $optionId,
                        'is_percent' => $isPercent,
                        'pricing_value' => $priceChange,
                    );
                }
            }
            $mainProduct->setConfigurableAttributesData($attributesData);
        }

        return $this;
    }
}
