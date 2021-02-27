<?php
class Biostore_Maispreco_Model_Observer
{
    /**
    * This action will generate maispreco.xml to save to <website>/var/maispreco/
    * @return void
    */
    public function sendProductTomaispreco()
    {
        $send_to_maispreco = Mage::getStoreConfig('catalog/maispreco/send_to_maispreco');
        $maispreco_parcelomento = Mage::getStoreConfig('catalog/maispreco/maispreco_parcelomento');
        $product_suffix_url = Mage::getStoreConfig('catalog/maispreco/product_suffix_url');
        $categories_excluded = Mage::getStoreConfig('catalog/webfocosp/categories_excluded');
        Mage::app()->setCurrentStore(1);

        // root element
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $root = $doc->createElement(Mage::app()->getStore()->getCode());
        $root = $doc->appendChild($root);

        /*
        if ($send_to_maispreco == 0) {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('enviar_para_maispreco', array('eq' => 1))
                ->addAttributeToFilter('status', 1);
        } else {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('status', 1);
        }*/
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        if ($send_to_maispreco == 0) {
        		die('send_to_maispreco').
            $query = "SELECT category_id, product_id, name
                    FROM catalog_category_product_index
                    INNER JOIN catalog_category_flat_store_1
                    ON (catalog_category_product_index.category_id = catalog_category_flat_store_1.entity_id)
                    WHERE catalog_category_product_index.category_id NOT IN (".$categories_excluded.") AND catalog_category_product_index.product_id IN (".$product_ids.") GROUP BY category_id";
            $products = $connection->fetchAll($query);
            $product_ids = '';
            foreach ($products as $product) {
                $product_ids .= $product['entity_id'].",";
            }
            $product_ids = trim($product_ids, ",");
            
            $query = "SELECT category_id, product_id, name
                    FROM catalog_category_product_index
                    INNER JOIN catalog_category_flat_store_1
                    ON (catalog_category_product_index.category_id = catalog_category_flat_store_1.entity_id)
                    WHERE catalog_category_product_index.product_id IN (".$product_ids.") GROUP BY category_id";
            $categories = $connection->fetchAll($query);
            
            $previousProductId = null;
            $categoryNames = array();
            foreach( $categories as $category )
            {
                $productId = $category['product_id'];

                if ($productId == $previousProductId)
                    continue;
                
                $categoryNames[$productId] = $category['name'];

                $previousProductId = $productId;
            }
            
        } else {

            $query = 
                "SELECT catalog_product_flat_1.*, CDL.value AS special_price_real
                 FROM catalog_product_flat_1
                 INNER JOIN catalog_product_entity_decimal AS CDL ON (CDL.entity_id = catalog_product_flat_1.entity_id AND CDL.attribute_id = 567)
                 where visibility = 4 GROUP BY catalog_product_flat_1.entity_id";
            
            $products = $connection->fetchAll($query);

            if($categories_excluded=='')
            {
            $query =
                "SELECT category_id, product_id, name
                 FROM catalog_category_product_index
                    INNER JOIN catalog_category_flat_store_1
                        ON (catalog_category_product_index.category_id = catalog_category_flat_store_1.entity_id)
                 ORDER BY product_id, catalog_category_product_index.position DESC;";
                
            }else{

            $query =
                "SELECT category_id, product_id, name
                 FROM catalog_category_product_index
                    INNER JOIN catalog_category_flat_store_1
                        ON (catalog_category_product_index.category_id = catalog_category_flat_store_1.entity_id)
                 WHERE category_id NOT IN (".$categories_excluded.") ORDER BY product_id, catalog_category_product_index.position DESC;";
            }
            $categories = $connection->fetchAll($query);

            $previousProductId = null;
            $categoryNames = array();
            foreach( $categories as $category )
            {
                $productId = $category['product_id'];

                if ($productId == $previousProductId)
                    continue;
                
                $categoryNames[$productId] = $category['name'];

                $previousProductId = $productId;
            }
        }
       	$controlProducts = array();
        if (count($products)) {
            foreach ($products as $product) {
	    
	    if(in_array($product['sku'], $controlProducts)){
            		continue;
            	}

                $produto = $doc->createElement('produto');
                $id_produto = $doc->createElement('id_produto');
                $id_produto->appendChild(
                    $doc->createTextNode($product['sku'])
                );
                $produto->appendChild($id_produto);

                $link_produto = $doc->createElement('link_produto');
                $link_produto->appendChild(
                    $doc->createTextNode(Mage::getBaseUrl() . $product['url_path'] . $product_suffix_url)
                );
                $produto->appendChild($link_produto);

                $titulo = $doc->createElement('titulo');
                $titulo->appendChild(
                    $doc->createTextNode($product['name'])
                );
                $produto->appendChild($titulo);

                $preco = $doc->createElement('preco');
                $price = $product['special_price_real'] ? $product['special_price_real'] : $product['price'];
                $preco->appendChild(
                    $doc->createTextNode(number_format($price,2))
                );
                $produto->appendChild($preco);

                $parcelamento = $doc->createElement('parcelamento');
                $parcelomento_value = '';
                $maximo = array();
                
                
                if ('bragspag' == $maispreco_parcelomento) {
                    $maximo = Mage::getModel('braspagcc/payment_gateway')->getParcelamentoMaximo2($price);
                } else if ('parcelamento' == $maispreco_parcelomento) {
                    $maximo = Mage::getModel('parcelamento/parcelamento')->getParcelamentoMaximo($price);
                }

                $parcelomento_value = (!empty($maximo['parcelas']) && !empty($maximo['valor'])) ? "Até ".trim($maximo['parcelas'])." x de ".trim($maximo['valor']) : '';

                $parcelamento->appendChild(
                    $doc->createTextNode($parcelomento_value)
                );
                $produto->appendChild($parcelamento);

                $imagem = $doc->createElement('imagem');
                $imageUrl = $product['thumbnail'] ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product['thumbnail'] : '';
                $imagem->appendChild(
                    $doc->createTextNode($imageUrl)
                );
                $produto->appendChild($imagem);


                $categoria = $doc->createElement('categoria');
                if (array_key_exists($product['entity_id'], $categoryNames))
                {
                    $category_name = $categoryNames[$product['entity_id']];
                }
                else {
                    $category_name = 'NA';
                }
                $categoria->appendChild(
                    $doc->createTextNode($category_name)
                );

                $produto->appendChild($categoria);

                $root->appendChild($produto);

                $controlProducts[] = $product['sku'];
            }
        }

        //header('Content-Type: text/xml');
        //echo $doc->saveXML();

        $path_to_save_xml = Mage::getBaseDir().'/maispreco/';
        if(is_dir($path_to_save_xml)){
            $doc->save($path_to_save_xml.'maispreco.xml');
        }
        else {
            mkdir($path_to_save_xml, 0777);
            $doc->save($path_to_save_xml.'maispreco.xml');
        }
        exit;
    } // eof sendProductTomaispreco
}
