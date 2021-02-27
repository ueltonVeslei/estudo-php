<?php
class EGBR_Zhao_Model_Observer
{
    /*
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
                    Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('enviar_para_zhao' => 0), 0);
                $count++;
                endif;
            endforeach;
        endif;
        echo 'Total: '.$count;
        exit;
    }
    */
    
	public function changeAttSet(){
		
		die();

		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('core_read');
	
		$count = 0;
		$query = "SELECT DISTINCT CPF1.sku FROM catalog_product_flat_1 AS CPF1
					INNER JOIN cataloginventory_stock_item csi ON ( csi.product_id = CPF1.entity_id )
					WHERE CPF1.visibility = 4 and csi.is_in_stock = 1";
	
		$produtos = $connection->query($query);
		
		if(count($produtos)){
			foreach($produtos as $value){
				
				$produto = Mage::getModel('catalog/product')->loadByAttribute('sku',$value['sku']);
				$categories = array();
				$array = array();
				$categories = $produto->getCategoryIds();

				$cat_excluded = Mage::getStoreConfig('catalog/zhao/categories_excluded');
				$cat_excluded = $cat_excluded.",";
				
				$cat = explode(",", $cat_excluded);
				
				$contarcat = count($cat);
				
				for ($i=0;$i<$contarcat;$i++){
					
					if(in_array($cat[$i], $categories)){
						Mage::getSingleton('catalog/product_action')
						->updateAttributes(array($produto->getId()),
								array('enviar_para_zhao' => 0), 0);
						$count++;
					}
					
				}
				
			}
		}
		echo 'Total: '.$count;
		exit;
	}
	
	
    /**
    * This action will generate cliquefarma.xml to save to <website>/var/cliquefarma/
    * @return void
    */
    public function sendProductTozhao()
    {
    	
    	//die();
    	
    	/*
    	ob_start();
		ob_end_clean();
		ob_start();
    	*/
    	
    	$send_to_zhao = Mage::getStoreConfig('catalog/zhao/send_to_zhao');
        $cat_excluded = Mage::getStoreConfig('catalog/zhao/categories_excluded');
        
        // root element
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $root = $doc->createElement(Mage::app()->getStore()->getCode());
        $root = $doc->appendChild($root);

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        //var_dump($send_to_zhao);
        //die();
        
        if ($send_to_zhao == 0) {
            $query = "SELECT CPF1.*, CDL.value AS special_price_real FROM catalog_product_flat_1 AS CPF1
                        INNER JOIN catalog_product_entity_int AS CPEI ON (CPF1.entity_id = CPEI.entity_id)
                        INNER JOIN eav_attribute AS EA ON (CPEI.attribute_id = EA.attribute_id)
                        INNER JOIN catalog_product_entity_decimal AS CDL ON (CDL.entity_id = CPF1.entity_id AND CDL.attribute_id = 567)
                        WHERE EA.attribute_code = 'enviar_para_zhao' AND CPEI.value = 1";
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
				WHERE EA.attribute_code = 'enviar_para_zhao' AND CPEI.value = 1 AND CPF1.visibility = 4 and csi.is_in_stock = 1";
            
            $products = $connection->fetchAll($query);

        }

        
        $contar = 0;
        $contando = 0;
        
        
        //var_dump(count($products));
        //die();
        
        if (count($products)) {
       
            foreach ($products as $product) {
            	
            	
            	/*
            	idLoja
            	codigoBarras
            	nomeCategoria
            	CategortiaPai
            	nomeProduto
            	marcaProduto
            	caracteristicasProduto
            	palavrasChaves
            	precoProduto
            	foto1Produto
            	foto2Produto
            	foto3Produto
            	foto4Produto
            	foto5Produto
            	statusProduto
            	*/
            	
            	//var_dump($product['sku']);
            	//die();
            	
            	
            	//$produtox = Mage::getModel('catalog/product');
            	//$produtox->load($product['entity_id']);
            	
            	/*
            	$teste = $produtox->getData();
            	echo '<pre>';
            	var_dump($teste);
            	echo '</pre>';
            	die();
            	*/
            	
            	$idfabricante = $product['manufacturer'];
            	
            	if ($idfabricante){
	            	$fabricante = $connection->fetchAll("SELECT *
												FROM eav_attribute_option_value
												WHERE option_id = '{$idfabricante}'");
	            	
	            	
	            	if (count($fabricante) > 0) {
	            		$nomefabricante = $fabricante[0]['value'];
	            	}else{
	            		$nomefabricante = " ";
	            	}
            	}
            	
            	
            	/*
            	$idsubcategorias = $produtox->getCategoryIds();
            	$contarsub = count($idsubcategorias) - 1;
            	
            	$categoria = Mage::getModel('catalog/category');
            	$subcategoria = Mage::getModel('catalog/category');
            	
            	if ($contarsub >= 0){
            		$subcategoria->load($idsubcategorias[$contarsub]);
            		$categoria->load($subcategoria->getParentId());
            	
            		$nomesubcategoria = $subcategoria->getName();
            		$nomecategoria = $categoria->getName();
            	
            	}else{
            	
            		$nomesubcategoria = "";
            		$nomecategoria = "";
            	
            	}
            	*/
            	
            	$nomesubcategoria = "";
            		$nomecategoria = "";
            	
            	$categories = array();
            	$sql = "SELECT CCP.category_id, CCEV.value as category_name FROM catalog_category_product CCP LEFT JOIN catalog_category_entity_varchar CCEV ON(CCP.category_id = CCEV.entity_id AND CCEV.attribute_id = 111) WHERE CCP.product_id = ".$product['entity_id'];
		        $catRes = $connection->fetchAll($sql);
		        foreach($catRes as $cat):
		            $categories['category_id'][] = $cat['category_id'];
		            $categories['category_name'][] = $cat['category_name'];
		        endforeach;
            	
		        //var_dump($categories);
		        //die();
		       
            	$produto = $doc->createElement('produto');
            	
            	$idLoja = $doc->createElement('idLoja');
            	$idLoja->appendChild(
            			$doc->createTextNode('300')
            	);
            	$produto->appendChild($idLoja);
            	
            	$idProduto = $doc->createElement('skuProduto');
            	$idProduto->appendChild(
            			$doc->createTextNode($product['sku'])
            	);
            	$produto->appendChild($idProduto);
            	
            	$codProduto = $doc->createElement('codProduto');
            	$codProduto->appendChild(
            			$doc->createTextNode($product['entity_id'])
            	);
            	$produto->appendChild($codProduto);
            	
            	$codigoBarras = $doc->createElement('codigoBarras');
            	$codigoBarras->appendChild(
            			$doc->createTextNode('')
            	);
            	$produto->appendChild($codigoBarras);
            	 
            	$nomeCategoria = $doc->createElement('nomeCategoria');
            	$nomeCategoria->appendChild(
            			$doc->createTextNode((count($categories) > 0)?implode(',',array_unique($categories['category_name'])):"")
            	);
            	$produto->appendChild($nomeCategoria);
            	
            	$categoriaPai = $doc->createElement('categoriaPai');
            	$categoriaPai->appendChild(
            			$doc->createTextNode($nomesubcategoria)
            	);
            	$produto->appendChild($categoriaPai);
            	
            	$nomeProduto = $doc->createElement('nomeProduto');
            	$nomeProduto->appendChild(
            			$doc->createTextNode($product['name'])
            	);
            	$produto->appendChild($nomeProduto);
            	
            	$marcaProduto = $doc->createElement('marcaProduto');
            	$marcaProduto->appendChild(
            			$doc->createTextNode($nomefabricante)
            	);
            	$produto->appendChild($marcaProduto);
            	
            	$caracteristicasProduto = $doc->createElement('caracteristicasProduto');
            	$caracteristicasProduto->appendChild(
            			$doc->createTextNode('')
            	);
            	$produto->appendChild($caracteristicasProduto);
            	
            	$palavrasChaves = $doc->createElement('palavrasChaves');
            	$palavrasChaves->appendChild(
            			$doc->createTextNode(str_replace("-", ",", $product['url_key']))
            	);
            	$produto->appendChild($palavrasChaves);
            	
            	
            	 //$price = $product['special_price_real'];
            	$price = $product['special_price_real'] ? $product['special_price_real'] : $product['price'];
            	$precoProduto = $doc->createElement('precoProduto');
            	$precoProduto->appendChild(
            			$doc->createTextNode(number_format($price,2, ',', '.'))
            	);
            	$produto->appendChild($precoProduto);
            	
            	$imageUrl = $product['thumbnail'] ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product['thumbnail'] : '';
                $imageUrl = str_replace('http://admin', 'http://www', $imageUrl);
            	$foto1Produto = $doc->createElement('foto1Produto');
            	$foto1Produto->appendChild(
            			$doc->createTextNode($imageUrl)
            	);
            	$produto->appendChild($foto1Produto);
            	
            	$foto2Produto = $doc->createElement('foto2Produto');
            	$foto2Produto->appendChild(
            			$doc->createTextNode('')
            	);
            	$produto->appendChild($foto2Produto);
            	
            	$foto3Produto = $doc->createElement('foto3Produto');
            	$foto3Produto->appendChild(
            			$doc->createTextNode('')
            	);
            	$produto->appendChild($foto3Produto);
            	
            	$foto4Produto = $doc->createElement('foto4Produto');
            	$foto4Produto->appendChild(
            			$doc->createTextNode('')
            	);
            	$produto->appendChild($foto4Produto);
            	
            	$foto5Produto = $doc->createElement('foto5Produto');
            	$foto5Produto->appendChild(
            			$doc->createTextNode('')
            	);
            	$produto->appendChild($foto5Produto);
            	
            	$statusProduto = $doc->createElement('statusProduto');
            	$statusProduto->appendChild(
            			$doc->createTextNode('online')
            	);
            	$produto->appendChild($statusProduto);
            	
            	
            	//$url = trim(Mage::getBaseUrl() . $produtox->getUrlPath() . '?utm_source=site&utm_medium=cpc&utm_campaign=zhao');
            	$url = trim(Mage::getBaseUrl() . $product['url_path'] . '?utm_source=site&utm_medium=cpc&utm_campaign=zhao');
                $url = str_replace('http://admin', 'http://www', $url);
            	$cddata = "<![CDATA[$url]]>";
            	
            	
            	$urlProduto = $doc->createElement('urlProduto');
            	$urlProduto->appendChild(
            			$doc->createTextNode($cddata)
            	);
            	$produto->appendChild($urlProduto);
            	
                $root->appendChild($produto);
                
                
                $contar++;
                $contando++;
            }
        }

        //header('Content-Type: text/xml');
        //echo $doc->saveXML();
        
        $path_to_save_xml = Mage::getBaseDir().'/zhao/';
        
        if(is_dir($path_to_save_xml)){
            $doc->save($path_to_save_xml.'zhao.xml');
        }
        else {
            mkdir($path_to_save_xml, 0777);
            $doc->save($path_to_save_xml.'zhao.xml');
        }
        
        echo 'total:'.$contando;
        exit;
    } // eof sendProductTotradepar
}
