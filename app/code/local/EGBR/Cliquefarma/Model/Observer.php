<?php
class EGBR_cliquefarma_Model_Observer
{
    
    public function changeAttSet(){
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        
        $count = 0;
        $query = 
           "SELECT DISTINCT CPF1.sku FROM catalog_product_flat_1 AS CPF1
            INNER JOIN cataloginventory_stock_item csi ON ( csi.product_id = CPF1.entity_id ) 
            WHERE CPF1.visibility = 4 and csi.is_in_stock = 1";

        $products = $connection->query($query);
        if($products):
            foreach($products as $product):
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$product['sku']);
                $categories = array();
                $categories = $product->getCategoryIds();
                
                // 26 -> tarjados, 295 -> antibacterianos - STeV
                if(in_array(26, $categories) || in_array(295, $categories)):
                    Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('enviar_para_cliquefarma' => 0), 0);
                $count++;
                endif;
            endforeach;
        endif;
        echo 'Total: '.$count;
        exit;
    }
    /**
    * This action will generate cliquefarma.xml to save to <website>/var/cliquefarma/
    * @return void
    */
    public function sendProductTocliquefarma()
    {
    	
    	
        $send_to_cliquefarma = Mage::getStoreConfig('catalog/tradeparpricecompare/send_to_cliquefarma');
        $cliquefarma_parcelomento = Mage::getStoreConfig('catalog/cliquefarma/cliquefarma_parcelomento');
        $product_suffix_url = Mage::getStoreConfig('catalog/cliquefarma/product_suffix_url');
        $categories_excluded = Mage::getStoreConfig('catalog/webfocosp/categories_excluded');
        
        // root element
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $root = $doc->createElement(Mage::app()->getStore()->getCode());
        $root = $doc->appendChild($root);

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        if ($send_to_cliquefarma == 0) {
            $query = "SELECT CPF1.*, CDL.value AS special_price_real FROM catalog_product_flat_1 AS CPF1
                        INNER JOIN catalog_product_entity_int AS CPEI ON (CPF1.entity_id = CPEI.entity_id)
                        INNER JOIN eav_attribute AS EA ON (CPEI.attribute_id = EA.attribute_id)
                        INNER JOIN catalog_product_entity_decimal AS CDL ON (CDL.entity_id = CPF1.entity_id AND CDL.attribute_id = 567)
                        WHERE EA.attribute_code = 'enviar_para_cliquefarma' AND CPEI.value = 1";
            $products = $connection->fetchAll($query);
            $product_ids = '';
            
            foreach ($products as $product) {
                $product_ids .= $product['entity_id'].",";
            }
            $product_ids = trim($product_ids, ",");
		
        } else {
            
            
            $query = 
                "SELECT CPF1.*, CDL.value AS special_price_real FROM catalog_product_flat_1 AS CPF1
				INNER JOIN catalog_product_entity_int AS CPEI ON (CPF1.entity_id = CPEI.entity_id)
				INNER JOIN eav_attribute AS EA ON (CPEI.attribute_id = EA.attribute_id)
				INNER JOIN cataloginventory_stock_item csi ON ( csi.product_id = CPF1.entity_id ) 
				INNER JOIN catalog_product_entity_decimal AS CDL ON (CDL.entity_id = CPF1.entity_id AND CDL.attribute_id = 567)
				WHERE EA.attribute_code = 'enviar_para_cliquefarma' AND CPEI.value = 1 AND CPF1.visibility = 4 and csi.is_in_stock = 1 limit 5";
            
            $products = $connection->fetchAll($query);

        }

        $id = '';
        $contar = 1;
        if (count($products)) {
            foreach ($products as $product) {
            	
            	$price = number_format($product['special_price_real'],2);
            	$price2 = number_format( $product['price'],2);
            	
            	if ($price == '0.00'){
            		
            		$price = $price2;
            		
            		if ($price == '0.00'){
            			continue;
            		}
            		
            	}
            	
            	
            	
            	if ($id == $product['sku']){
            		continue;
            	}
            	
            	
            	$id = $product['sku'];
            	
            	
            	$sqlean = "SELECT * FROM `catalog_product_ean` WHERE `sku` = '{$id}'";
            	$ean = $connection->fetchAll($sqlean);
            	
            	//var_dump($ean);
            	//die();
            	
            	
            	/*
            	echo $product['special_price_real'];
            	echo ' - ';
            	echo $product['special_price'];
            	die();
            	*/
            	
            	/*
            	$queryspecialprice = "SELECT value 
										FROM `catalog_product_entity_decimal` 
										WHERE `attribute_id` =567
										AND `entity_id` =$id";
            	 $value = $connection->fetchAll($queryspecialprice);
            	*/
            	
            	/*
            	$produto = Mage::getModel('catalog/product');
            	$produto->load($product['entity_id']);
            	
            	$specialprice = $produto->getSpecialprice();
            	$xspecialprice = $produto->getPrice();
            	*/

            	
            	
                $produto = $doc->createElement('produto');
                
                // Product_SKU - STeV
                $id_produto = $doc->createElement('oferta_id');
                $id_produto->appendChild(
                    $doc->createTextNode($product['sku'])
                );
                $produto->appendChild($id_produto);
                
                // Product name - STeV
                $titulo = $doc->createElement('oferta_descricao');
                $titulo->appendChild(
                    $doc->createTextNode($product['name'])
                );
                $produto->appendChild($titulo);

                // Empresa(fixo 54 -> Farma) - STeV
                $empresa = $doc->createElement('empresa_id');
                $empresa->appendChild(
                    $doc->createTextNode(54)
                );
                $produto->appendChild($empresa);

                
               
                
                
                // Product price/special price - STeV
                $preco = $doc->createElement('oferta_valor');
                
                $preco->appendChild(
                    $doc->createTextNode($price)
                );
                $produto->appendChild($preco);
                
                // Data d/m/Y h:i - STeV
                $data = $doc->createElement('oferta_data');
                $data->appendChild(
                    $doc->createTextNode(date('d/m/Y h:i'))
                );
                $produto->appendChild($data);
                
                $link_modificado = Mage::getBaseUrl() . $product['url_path'] . $product_suffix_url;
                $link_modificado = str_replace('http://admin', 'http://www', $link_modificado);
                
                // Product Link - STeV
                $link_produto = $doc->createElement('link_produto');
                $link_produto->appendChild(
                    $doc->createTextNode($link_modificado)
                );
                $produto->appendChild($link_produto);

                // Product image - STeV
                $imagem = $doc->createElement('oferta_imgproduto');
                $imageUrl = $product['thumbnail'] ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product['thumbnail'] : '';
                $imageUrl = str_replace('http://admin', 'http://www', $imageUrl);
                $imagem->appendChild(
                    $doc->createTextNode($imageUrl)
                );
                $produto->appendChild($imagem);
                
                // Principio Ativo do Produto
                $principioativo = $doc->createElement('oferta_principio_ativo');
                $principioativo->appendChild(
                		$doc->createTextNode($product['composicao_new'])
                );
                $produto->appendChild($principioativo);

                // Código do Ministério da Saude
                $ministerio = $doc->createElement('oferta_codigo_ms');
                $ministerio->appendChild(
                		$doc->createTextNode($product['nr_ministerio_da_saude'])
                );
                $produto->appendChild($ministerio);
                
                // Código de Barras
                $codigobarra = $doc->createElement('oferta_codigo_barra');
                $codigobarra->appendChild(
                		$doc->createTextNode('')
                );
                $produto->appendChild($codigobarra);
                
                $nada = '';
                
                if (count($ean) > 0){
                	$nada = $ean[0]['ean'];
                }else{
                	$nada = '';
                }
                
                $eanxml = $doc->createElement('ean');
                $eanxml->appendChild($doc->createTextNode($nada));
                $produto->appendChild($eanxml);
                $root->appendChild($produto);
                
              //  $contar++;
                
              // if ($contar == '5000') die();
            }
        }

        //header('Content-Type: text/xml');
        //echo $doc->saveXML();
        
        $path_to_save_xml = Mage::getBaseDir().'/cliquefarma/';
        
        if(is_dir($path_to_save_xml)){
            $doc->save($path_to_save_xml.'cliquefarma.xml');
        }
        else {
            mkdir($path_to_save_xml, 0777);
            $doc->save($path_to_save_xml.'cliquefarma.xml');
        }
        
        exit;
    } // eof sendProductTotradepar
}