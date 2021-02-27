<?php
class Onestic_Elastic_Exporter {

    protected $_excludeAttributes = array(
        'sku','name','description','status','price','cost','weight','volume_altura','volume_comprimento',
        'volume_largura','manufacturer','codigo_barras','visibility', 'created_at', 'updated_at',
        'entity_type_id','attribute_set_id','entity_id','short_description','old_id','news_from_date',
        'news_to_date','url_key','url_path','country_of_manufacture','category_ids','required_options',
        'has_options','image_label','small_image_label','thumbnail_label','image','small_image','thumbnail',
        'media_gallery','msrp_enabled','msrp_display_actual_price_type','enable_googlecheckout','tax_class_id',
        'gallery','custom_design','custom_design_from','custom_design_to','custom_layout_update','page_layout',
        'options_container','gift_message_available','msrp','is_recurring','recurring_profile','group_price',
        'tier_price','skyhub_send','description_standout','video'
    );

    protected $_elasticApi = null;
    protected $_configs = null;

    public function export() {
        $this->_getElasticApi()->setIndexName($this->getNameIndex());

        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect("*")
            ->addAttributeToFilter('visibility', array('in' => array(2,4)))
            ->addAttributeToFilter('type_id',array('in' => array('configurable','simple', 'virtual')))
            ->addAttributeToSort('updated_at', 'ASC');
        $products->setPageSize(200)->setCurPage(1);

        $allProducts = [];
        $count = $success = $errors = 0;
        foreach ($products as $prd) {
            try {
            	$product = Mage::getModel('catalog/product')->load($prd->getId());
		        $allProducts[] = $this->_getProductData($product);
		        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
		        $write->update('catalog_product_entity',['updated_at' => date('Y-m-d H:i:s')],'entity_id = ' . $product->getId());
		    	$write->update('catalog_product_flat_1',['updated_at' => date('Y-m-d H:i:s')],'entity_id = ' . $product->getId());
                $write->update('skyhub_products',[
                        'updated_at' => date('Y-m-d H:i:s'),
                        'status_sync' => 'NÃO'
                    ],
                    'product_id = ' . $product->getId());

                $success++;
            } catch (Exception $e) {
                Mage::log('EXPORT ERROR: ' . $e->getMessage(), null, 'export_elastic.log');
                echo $e;
                $errors++;
            }
        }

        $this->_getElasticApi()->mapperUntouchedField('product','url_key');
		$this->_getElasticApi()->sendProducts('product_id', $allProducts);
        echo $success . ' ENVIADOS COM SUCESSO | ' . $errors . ' ERROS' . PHP_EOL;
    }

    // protected function _send($productId) {
    //     $product = Mage::getModel('catalog/product')->load($productId);
    //     $productData = $this->_getProductData($product);
    //     $this->_getElasticApi()->mapperUntouchedField('product','url_key');
    //     $this->_getElasticApi()->sendProducts('product_id', [$productData]);
    //     $write = Mage::getSingleton('core/resource')->getConnection('core_write');
    //     $write->update('catalog_product_entity',array('updated_at' => date('Y-m-d H:i:s')),'entity_id = ' . $product->getId());
    // 	$write->update('catalog_product_flat_1',array('updated_at' => date('Y-m-d H:i:s')),'entity_id = ' . $product->getId());
    // }

    protected function _getConfig($name) {
        if (!$this->_configs) {
            $this->_configs = json_decode(file_get_contents(ABSOLUTE_PATH . '/config.json'));
        }

        return $this->_configs->{$name};
    }

    protected function getNameIndex(){
        return $this->_getConfig('name') . '-product';
    }

    protected function _getElasticApi() {
        if (!$this->_elasticApi) {
            $this->_elasticApi = new ElasticIndex($this->_getConfig('host'), $this->_getConfig('user'), $this->_getConfig('pass'));            
        }
        $this->_elasticApi->setIndexName($this->getNameIndex());

        return $this->_elasticApi;
    }

    protected function _getProductData($product) {
        $categories = $this->_getCategories($product);
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $productData = array(
            'product_id'            => $product->getId(),
            'sku'                   => $product->getSku(),
            'name'                  => $product->getName(),
            'description'           => $product->getDescription(),
            'short_description'     => $product->getShortDescription(),
            'status'                => $product->getStatus(),
            'qty'                   => $stock->getQty(),
            'price'                 => $product->getPrice(),
            'promotional_price'     => $product->getFinalPrice(),
            'tier_price'            => $product->getFormatedTierPrice(),
            'cost'                  => $product->getCost(),
            'weight'                => $product->getWeight(),
            'height'                => $product->getVolumeAltura(),
            'width'                 => $product->getVolumeComprimento(),
            'length'                => $product->getVolumeLargura(),
            'brand'                 => $product->getAttributeText('manufacturer'),
            'ean'                   => $product->getCodigoBarras(),
            'url_key'               => $product->getUrlKey(),
            'updated_at'            => $product->getUpdatedAt(),
            'created_at'            => $product->getCreatedAt(),
            'categories'            => $categories,
            'images'                => $this->_getImages($product),
            'specifications'        => $this->_getSpecifications($product),
            'custom_options'        => $this->_getCustomOptions($product),
            'relateds'              => $this->_getLinkedProducts($product, 'Related'),
            'crosssell'             => $this->_getLinkedProducts($product, 'CrossSell'),
            'upsell'                => $this->_getLinkedProducts($product, 'UpSell'),
        );

        if ($product->getTypeId() == 'configurable') {
            $productData['variations'] = $this->_getVariations($product);
        }

        return $productData;
    }

    protected function _getCategories($product) {
        $categories = [];
        foreach ($product->getCategoryIds() as $cat) {
            $category = Mage::getModel('catalog/category')->load($cat);
            $categories[] = [
                'code' => $category->getId(),
                'name' => $category->getName()
            ];
        }

        return $categories;
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
        $specs = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if (!in_array($code, $this->_excludeAttributes)) {
                $value = $attribute->getFrontend()->getValue($product);
                if ($value) {
                    $specs[$code] = $value;
                }
            }
        }

        return $specs;
    }

    protected function _getVariations($product) {
        $superAttributesAll = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);


        return array_reduce($superAttributesAll, function ($variations, $variation) {
            $variation['values'] = array_map(function ($value) use ($variation) {
                $value['attribute_id'] = $variation['attribute_id'];
                $value['id'] = $variation['id'];
                return $value;
            }, $variation['values']);

            $variations[$variation['attribute_code']] = $variation['values'];

            return $variations;
        }, []);
    }

    protected function _getCustomOptions($product) {
        $customOptions = [];
        foreach ($product->getOptions() as $option) {
            $customOption = [
                'option_id' => $option->getId(),
                'type'      => $option->getType(),
                'title'     => $option->getTitle(),
                'values'    => [],
            ];
            foreach ($option->getValues() as $value) {
                $customOption['values'][] = $value->getData();
            }
            $customOptions[] = $customOption;
        }

        return $customOptions;
    }

    protected function _getLinkedProducts($product, $type='Related') {
        $collection = $product->{'get' . $type . 'ProductIds'}();
        $products = [];
        foreach ($collection as $linkedId) {
            $linkedProduct = Mage::getModel('catalog/product')->load($linkedId);

            $products[] = [
                'product_id'            => $linkedProduct->getId(),
                'sku'                   => $linkedProduct->getSku(),
                'name'                  => $linkedProduct->getName(),
                'status'                => $linkedProduct->getStatus(),
                'price'                 => $linkedProduct->getPrice(),
                'promotional_price'     => $linkedProduct->getFinalPrice(),
                'ean'                   => $linkedProduct->getCodigoBarras(),
                'url_key'               => $linkedProduct->getUrlKey(),
                'images'                => $this->_getImages($linkedProduct),
            ];
        }

        return $products;
    }
}

