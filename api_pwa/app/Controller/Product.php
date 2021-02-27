<?php
class Controller_Product extends Controller {

	// Retornar os dados dos pedidos
	protected function _get() {
		if ($productURL = $this->getData('URL')) {
            $product = Mage::getModel('catalog/product')
            	->getCollection()
            	->addAttributeToSelect('*')
            	->addAttributeToFilter('url_path', $productURL.'.html')
            ->load();

            $product = array_values($product->toArray())[0];
            $gallery_images = Mage::getModel('catalog/product')->load($product['entity_id'])->getMediaGalleryImages();
			$items = [];
			foreach($gallery_images as $g_image) {
			    $items[] = $g_image['url'];
			}
			$product['images'] = $items;
            $image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product['entity_id'], 'image', Mage::app()->getStore()->getId());

			if($image){
				$product['image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
			}

            $this->setResponse('status', Standard::STATUS200);
			return $this->setResponse('data', $product);

		} else if ($category_id = $this->getData('CAT')) {
			$category = Mage::getModel('catalog/category')->load($category_id);
			if ($category === null) {
				$this->setResponse('status', Standard::STATUS200);
				return $this->setResponse('data',[]);
			}
			$products = Mage::getModel('catalog/product')->getCollection()
		        ->setStoreId(Mage::app()->getStore()->getId())
		        ->addAttributeToSelect('*')
		        ->addCategoryFilter($category)
		    ->load();

		    $products = $products->toArray();
		    foreach ($products as $key => $product) {
		    	$gallery_images = Mage::getModel('catalog/product')->load($product['entity_id'])->getMediaGalleryImages();
				$items = [];
				foreach($gallery_images as $g_image) {
				    $items[] = $g_image['url'];
				}
				$product['images'] = $items;
	            $image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product['entity_id'], 'image', Mage::app()->getStore()->getId());

				if($image){
					$product['image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
				}
				$products[$key] = $product;
		    }

			$this->setResponse('status', Standard::STATUS200);
			return $this->setResponse('data', $products);
		}

		$this->setResponse('status', Standard::STATUS500);
		return $this->setResponse('data','Dados inválidos');
	}
}