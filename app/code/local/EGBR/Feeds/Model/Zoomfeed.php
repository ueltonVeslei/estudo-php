<?php
/**
 * @desc Model gerar feed XML para Zoom - STeV
 * 
 * @author Estevam Neves
 *
 */
final class EGBR_Feeds_Model_Zoomfeed extends EGBR_Feeds_Model_GerarFeed{
    /**
    * This action will generate zoom.xml to save to <website>/var/zoom/
    * @return void
    */
    private $count;
    private $manufaturers;
    
    public function getStoreAttributes(){
        $attArr = array('description','special_price');
        
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        
        $sql_att = "
            SELECT attribute_id, attribute_code, backend_type 
            FROM eav_attribute
            WHERE entity_type_id = ".$this->product_entity_key." AND ( attribute_code = 'name'
        ";
        
        foreach($attArr as $att) $sql_att .= " OR attribute_code = '".$att."' ";
        $sql_att .= ")";
        
        return $readConnection->fetchAll($sql_att);
    }
    
    private function getCustomStoreCollection(){
        $default_args = array(
            'website_id' => 1,
            'status' => 1,
            'limit' => false
        );
        
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $attResArr = $this->getStoreAttributes();
        
        // Main SQL - STeV
        $sql_join = "";
        
        for($i=0;$i<count($attResArr);$i++):
            $attEl = "att".$i;
            $sql_selectArr[] = " $attEl.value as ".$attResArr[$i]['attribute_code'];
            $sql_join .= " LEFT JOIN catalog_product_entity_".$attResArr[$i]['backend_type']." $attEl ON( $attEl.entity_id = CPF.entity_id AND $attEl.attribute_id = ".$attResArr[$i]['attribute_id'].")";
        endfor;
        
        $sql = "SELECT CPF.entity_id as ID, CPF.sku, CPF.short_description, CPF.nr_ministerio_da_saude, CPF.price, CPF.url_path, CPF.small_image, CPEI_status.value, CISI.qty, CISI.is_in_stock, CDL.value AS special_price_real,";
        if($sql_selectArr) $sql .= implode(',',$sql_selectArr);
        // Status 1.4 = 69, 1.7 = 273
        $sql .= "
                FROM catalog_product_flat_1 as CPF
                LEFT JOIN catalog_product_entity_int CPEI_status ON( CPEI_status.entity_id = CPF.entity_id AND CPEI_status.attribute_id = 273)
                LEFT JOIN catalog_product_website CPW ON(CPW.product_id = CPF.entity_id)
                LEFT JOIN catalog_product_entity_int CPEI_zoomfeed ON( CPEI_zoomfeed.entity_id = CPF.entity_id AND CPEI_zoomfeed.attribute_id = 1041)
                LEFT JOIN cataloginventory_stock_item CISI ON(CISI.product_id = CPF.entity_id)
                LEFT JOIN catalog_product_entity_decimal AS CDL ON (CDL.entity_id = CPF.entity_id AND CDL.attribute_id = 567)";
        $sql .= $sql_join;
        $sql .= "
                WHERE CPEI_status.value = ".$default_args['status']." 
                AND CPEI_zoomfeed.value = 1
                AND CPW.website_id = ".$default_args['website_id']." 
                AND CPF.visibility = 4
                GROUP BY CPF.entity_id 
                ORDER BY CPEI_status.value DESC";
        if($default_args['limit']) $sql .= " LIMIT ".$default_args['limit'];
        
        
        return $readConnection->fetchAll($sql);
    }
    
    public function toZoom(){
    	
    	ob_start();
		ob_end_clean();
		ob_start();
        
        $send_zoom = Mage::getStoreConfig('catalog/zoomfeed/send_to_zoom');
        if($send_zoom == 0) die("A atualziação de XML da Zoom encontra-se desabilitada.");
        parent::initDoc();
                
//        $_products = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter(array(array('attribute' => 'enviar_para_zoom','eq' => '1')))->getAllIds();
        $_products = $this->getCustomStoreCollection();
//        ->addFieldToFilter('visibility',array('eq'=>4))
//        ->getSelectSql(true)
        
        echo'<br />Início: '.date('d/m/Y h:i:s');
        echo'<br />Zoom product count: '.count($_products);
        $this->count = 0;
        if(count($_products)) $this->formatProductsFeed($_products);
        echo'<br />Fim: '.date('d/m/Y h:i:s');
        echo'<br />Exportados para o xml: '.$this->count;
        parent::saveDoc("zoom");
    }
    
    private function isInvalidCategory($catIds){
        $invalidIds = explode(',',Mage::getStoreConfig('catalog/zoomfeed/categories_excluded'));
        if(!$invalidIds || !$catIds) return false;
        
        foreach($catIds as $catId):
            if(in_array($catId, $invalidIds)) return true;
        endforeach;
        return false;
    }
    
    private function getProductCategoryIds($product_id){
        $categories = array();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        
        // 1.4 = 22, 1.7 = 111
        $sql = "SELECT CCP.category_id, CCEV.value as category_name FROM catalog_category_product CCP LEFT JOIN catalog_category_entity_varchar CCEV ON(CCP.category_id = CCEV.entity_id AND CCEV.attribute_id = 111) WHERE CCP.product_id = ".$product_id;
        $catRes = $readConnection->fetchAll($sql);
        foreach($catRes as $cat):
            $categories['category_id'][] = $cat['category_id'];
            $categories['category_name'][] = $cat['category_name'];
        endforeach;
        
        return $categories;
    }
    
    private function formatProductsFeed($_products){
        $product_sufix = Mage::getStoreConfig('catalog/zoomfeed/product_suffix_url');
        foreach ($_products as $_product):
            $cat_arr = array();
            $_prod = array();
            
            
            $categories = array();
            $categories = $this->getProductCategoryIds($_product['ID']);
//            $_product = Mage::getModel('catalog/product')->load($_prodId);
//            $categories = $_product->getCategoryIds();
//            
            if(count($categories) > 0) if($this->isInvalidCategory($categories['category_id'])) continue;
            $this->count++;
            
//            $_prodStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);

//            foreach($categories as $catID):
//                $category = Mage::getModel('catalog/category')->load($catID);
//                if($category->getName()) $cat_arr[] = $category->getName();
//            endforeach;

            $_prod['CODIGO'] = $_product['ID'];
            $_prod['NOME_DO_PRODUTO'] = $_product['name'];
            $preco_final = ($_product['special_price_real'])?$_product['special_price_real']:$_product['price'];
            $_prod['PRECO'] = number_format($preco_final, 2, ',', '.');
            $_prod['URL'] = Mage::getUrl($_product['url_path']).$product_sufix;
            $_prod['URL_IMAGEM'] = Mage::getUrl("media/catalog/product").$_product['small_image'];
            $_prod['DEPARTAMENTO'] = (count($categories) > 0)?implode(',',array_unique($categories['category_name'])):"";
            $_prod['SUBDEPARTAMENTO'] = "";
            $_prod['DESCRICAO'] = $_product['description'];
            $_prod['PRECO_DE'] = number_format($_product['special_price'], 2, ',', '.');
            $_prod['SKU'] = $_product['sku'];

            parent::productsToDoc($_prod);
//            if($this->count == 5) break;
        endforeach;
    }
    
    public function gerarZoomXml()
    {
    
    	$product_sufix = Mage::getStoreConfig('catalog/zoomfeed/product_suffix_url');
    	
    	//$send_to_cliquefarma = Mage::getStoreConfig('catalog/tradeparpricecompare/send_to_cliquefarma');
        //$cliquefarma_parcelomento = Mage::getStoreConfig('catalog/cliquefarma/cliquefarma_parcelomento');
        //$product_suffix_url = Mage::getStoreConfig('catalog/cliquefarma/product_suffix_url');
        //$categories_excluded = Mage::getStoreConfig('catalog/webfocosp/categories_excluded');
        
        // root element
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $root = $doc->createElement(Mage::app()->getStore()->getCode());
        $root = $doc->appendChild($root);

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
            
        $query = 
                "SELECT CPF1.*, CDL.value AS special_price_real FROM catalog_product_flat_1 AS CPF1
                        INNER JOIN catalog_product_entity_int AS CPEI ON (CPF1.entity_id = CPEI.entity_id)
                        INNER JOIN eav_attribute AS EA ON (CPEI.attribute_id = EA.attribute_id)
                        INNER JOIN catalog_product_entity_decimal AS CDL ON (CDL.entity_id = CPF1.entity_id AND CDL.attribute_id = 567)
                        WHERE EA.attribute_code = 'enviar_para_cliquefarma' AND CPEI.value = 1";
            
        $products = $connection->fetchAll($query);

        //var_dump(count($products));
        //die();
        
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
            	
            	
            	
            	$categories = array();
            	$sql = "SELECT CCP.category_id, CCEV.value as category_name FROM catalog_category_product CCP LEFT JOIN catalog_category_entity_varchar CCEV ON(CCP.category_id = CCEV.entity_id AND CCEV.attribute_id = 111) WHERE CCP.product_id = ".$product['entity_id'];
		        $catRes = $connection->fetchAll($sql);
		        foreach($catRes as $cat):
		            $categories['category_id'][] = $cat['category_id'];
		            $categories['category_name'][] = $cat['category_name'];
		        endforeach;
            	
		        if(in_array(26, $categories['category_id']) || in_array(295, $categories['category_id'])):
                	continue;    
                endif;
            	
            	
                $produto = $doc->createElement('produto');
                
                $cod_produto = $doc->createElement('CODIGO');
                $cod_produto->appendChild(
                    $doc->createTextNode($product['entity_id'])
                );
                $produto->appendChild($cod_produto);
                
                
                $nome_produto = $doc->createElement('NOME_DO_PRODUTO');
                $nome_produto->appendChild(
                    $doc->createTextNode($product['name'])
                );
                $produto->appendChild($nome_produto);
                
                
                $preco_produto = $doc->createElement('PRECO');
                $preco_produto->appendChild(
                    $doc->createTextNode($price)
                );
                $produto->appendChild($preco_produto);
                
                
                $url_produto = $doc->createElement('URL');
                $url_produto->appendChild(
                    $doc->createTextNode(Mage::getBaseUrl() . $product['url_path'] . $product_sufix)
                );
                $produto->appendChild($url_produto);
                
                
                $imageUrl = $product['thumbnail'] ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product['thumbnail'] : '';
                $imagem_produto = $doc->createElement('URL_IMAGEM');
                $imagem_produto->appendChild(
                    $doc->createTextNode($imageUrl)
                );
                $produto->appendChild($imagem_produto);
                
                
                $departamento_produto = $doc->createElement('DEPARTAMENTO');
                $departamento_produto->appendChild(
                    $doc->createTextNode((count($categories) > 0)?implode(',',array_unique($categories['category_name'])):"") //(count($categories) > 0)?implode(',',array_unique($categories['category_name'])):"")
                );
                $produto->appendChild($departamento_produto);
                
                
                $subdepartamento_produto = $doc->createElement('SUBDEPARTAMENTO');
                $subdepartamento_produto->appendChild(
                    $doc->createTextNode("")
                );
                $produto->appendChild($subdepartamento_produto);
                
                
                $descricao_produto = $doc->createElement('DESCRICAO');
                $descricao_produto->appendChild(
                    $doc->createTextNode($product['description'])
                );
                $produto->appendChild($descricao_produto);
                
                
                $precode_produto = $doc->createElement('PRECO_DE');
                $precode_produto->appendChild(
                    $doc->createTextNode($price)
                );
                $produto->appendChild($precode_produto);
                
                
                $sku_produto = $doc->createElement('SKU');
                $sku_produto->appendChild(
                    $doc->createTextNode($product['sku'])
                );
                $produto->appendChild($sku_produto);
                
                $root->appendChild($produto);
                
              //  $contar++;
                
              // if ($contar == '5000') die();
            }
        }

        //header('Content-Type: text/xml');
        //echo $doc->saveXML();
        
        $path_to_save_xml = Mage::getBaseDir().'/zoom/';
        
        if(is_dir($path_to_save_xml)){
            $doc->save($path_to_save_xml.'zoomteste.xml');
        }
        else {
            mkdir($path_to_save_xml, 0777);
            $doc->save($path_to_save_xml.'zoomteste.xml');
        }
        
        exit;
    } // eof sendProductTotradepar
    
}