<?php
class Onestic_Performa_Model_Queue_Updater extends Varien_Object {

    const PERFORMA_API_URL = 'https://feed.performa.ai/v2/';

    private $_excludeAttributes = array(
        'sku','name','description','status','price','cost','weight','volume_altura','volume_comprimento',
        'volume_largura','manufacturer','codigo_barras','visibility','created_at','update_at','type_id',
        'entity_type_id','attribute_set_id','entity_id','short_description','old_id','news_from_date',
        'news_to_date','url_key','url_path','country_of_manufacture','category_ids','required_options',
        'has_options','image_label','small_image_label','thumbnail_label','image','small_image','thumbnail',
        'media_gallery','msrp_enabled','msrp_display_actual_price_type','enable_googlecheckout','tax_class_id',
        'gallery','custom_design','custom_design_from','custom_design_to','custom_layout_update','page_layout',
        'options_container','gift_message_available','msrp','is_recurring','recurring_profile','group_price',
        'tier_price','updated_at','performa_send','description_standout','video'
    );
    
	private $_qtyRegs = 50;
	
	protected function _syncPerforma($product) {
        $params = [
            'type'      => 'feed',
            'pubkey'    => '0D95BE80-D0EC-01F2-B2B5-030149FB2B53',
            'params'    => json_encode([
                'route'     => 'update/single',
                'data'      => $product
            ])
        ];

        $postData = http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::PERFORMA_API_URL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $exec = curl_exec($ch);
        curl_close($ch);
    }
    
    public function populate() {
	    Mage::getModel('onestic_performa/queue')->populate();
	}
	
	public function products() {
		$products = Mage::getModel('onestic_performa/queue')->getCollection()
						->addFieldToFilter('product_id', array('notnull' => true))
						->setCurPage(1);
		$products->getSelect()->order('updated_at ASC');
		$products->getSelect()->limit($this->_qtyRegs);		

		foreach ($products as $prd) {
			$this->_send($prd->getProductId());
			$this->unqueueProduct($prd->getId());
		}
    }
    
    protected function unqueueProduct($queueItem)
    {
        $item = Mage::getModel('onestic_performa/queue')->load($queueItem);
        $item->delete();
    }
	
	public function export($page=NULL) {
		$products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect("*")
            ->addAttributeToFilter('visibility', array('in' => array(3,4)))
            ->addAttributeToFilter('type_id',array('in' => array('configurable','simple')))
            ->addAttributeToSort('updated_at', 'DESC');
        $products->setPageSize(200)
            ->setCurPage(1);

        $count = $success = $errors = 0;
        foreach ($products as $prd) {
            try {
                $this->_send($prd->getId());
                Mage::log('EXPORT SUCCESS: ' . $prd->getId(), null, 'performa_queue_success.log');
                $success++;
            } catch (Exception $e) {
                Mage::log('EXPORT ERROR: ' . $e->getMessage(), null, 'performa_queue_error.log');
                $errors++;
            }
        }
        echo 'PERFORMA.AI: ' . $success . ' ENVIADOS COM SUCESSO | ' . $errors . ' ERROS' . PHP_EOL;
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

	public function queue($productId) {
        $control = Mage::getModel('onestic_performa/queue')->load($productId, 'product_id');
        if (!$control->getId()) {
            Mage::log('PRODUCT SEND :: NOT EXISTS ' . $productId,null,'performa_sync_prd.log');
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getId()) {
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                $prd = new stdClass();
                $prd->sku = $product->getSku();
                $prd->name = $product->getName();
                $prd->status = (($product->getStatus() == 1) ? "enabled" : "disabled");
                $prd->qty = $stock->getQty();
                $prd->price = $product->getPrice();
                $prd->promotional_price = $product->getFinalPrice();
                Mage::getModel('onestic_performa/queue')->create($prd);
                $control = Mage::getModel('onestic_performa/queue')->load($productId, 'product_id');
            }
        }
	}
	
	protected function _send($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);
        $productData = $this->_getProductData($product);
        $this->_syncPerforma($productData);
    }
	
    protected function _getProductData($product) {
        $categories = $this->_getCategories($product);
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $performaSend = $product->getPerformaSend();
        $productData = array(
            'product_id'            => $product->getId(),
            'sku'                   => $product->getSku(),
            'name'                  => $product->getName(),
            'description'           => $product->getDescription(),
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
            'categories'            => $categories,
            'images'                => $this->_getImages($product),
            'specifications'        => $this->_getSpecifications($product),
            'custom_options'        => $this->_getCustomOptions($product),
            'performa_send'         => ($product->getPerformaSend()) ? 'Yes' : 'No',
            //'relateds'              => $this->_getLinkedProducts($product, 'Related'),
            //'crosssell'             => $this->_getLinkedProducts($product, 'CrossSell'),
            //'upsell'                => $this->_getLinkedProducts($product, 'UpSell'),
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
        $_categoryIds = $product->getCategoryIds();
        if (in_array(26, $_categoryIds)) {
            echo 'https://www.farmadelivery.com.br/media/wysiwyg/Imagens/produto-sem-imagem-controle-especial-farmadelivery-277.jpg';
        }
        elseif(in_array(295, $_categoryIds)) {
            echo 'https://www.farmadelivery.com.br/media/wysiwyg/Imagens/produto-sem-imagem-antimicrobiano-farmadelivery-277.jpg';
        }
        elseif(in_array(14, $_categoryIds) && in_array(28, $_categoryIds)){
            echo 'https://www.farmadelivery.com.br/media/wysiwyg/Imagens/caixa-genericos-tarjados-farmadelivery-277_1.jpg';
        }
        elseif(in_array(28, $_categoryIds)) {
            echo 'https://www.farmadelivery.com.br/media/wysiwyg/caixa-tarjados-farmadelivery.jpg';
        }
        else {
            $images = array();
            foreach ($product->getMediaGalleryImages() as $image) {
                $images[] = $image->getUrl();
            }

            return $images;
        } 
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
	
	public function sync($productId) {
		return $this->_send($productId);
	}
	
}