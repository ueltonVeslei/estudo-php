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
        $send_to_cliquefarma = Mage::getStoreConfig('catalog/cliquefarma/send_to_cliquefarma');
        
        $cliquefarma_parcelomento = Mage::getStoreConfig('catalog/cliquefarma/cliquefarma_parcelomento');
        $product_suffix_url = Mage::getStoreConfig('catalog/cliquefarma/product_suffix_url');
        $categories_excluded = Mage::getStoreConfig('catalog/cliquefarma/categories_excluded');
        
        // root element
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $root = $doc->createElement(Mage::app()->getStore()->getCode());
        $root = $doc->appendChild($root);

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        if ($send_to_cliquefarma == 0) {
            
            $products = 0;
            
        } else {
            
            $products =  Mage::getModel('catalog/product')->getCollection()
            			->addAttributeToFilter('enviar_para_cliquefarma', array( 'eq' => '1'));
            
        }

        if (count($products)) {
            foreach ($products as $product) {
            	
            	$produtox = Mage::getModel('catalog/product');
            	$produtox->load($product['entity_id']);
            	
            	$categories = $produtox->getCategoryIds();
            	
            	if(in_array(26, $categories) || in_array(295, $categories)){
            		continue;
            	}
            	            	
                $produto = $doc->createElement('produto');
                
                $id_produto = $doc->createElement('oferta_id');
                $id_produto->appendChild(
                    $doc->createTextNode($produtox->getSku())
                );
                $produto->appendChild($id_produto);
                
                $titulo = $doc->createElement('oferta_descricao');
                $titulo->appendChild(
                    $doc->createTextNode($produtox->getName())
                );
                $produto->appendChild($titulo);

                $empresa = $doc->createElement('empresa_id');
                $empresa->appendChild(
                    $doc->createTextNode(54)
                );
                $produto->appendChild($empresa);

                $preco = $doc->createElement('oferta_valor');
                $price = $produtox->getSpecialPrice();
                $preco->appendChild(
                    $doc->createTextNode(number_format($price,2))
                );
                $produto->appendChild($preco);
                
                $data = $doc->createElement('oferta_data');
                $data->appendChild(
                    $doc->createTextNode(date('d/m/Y h:i'))
                );
                $produto->appendChild($data);
                
                $link_produto = $doc->createElement('link_produto');
                $link_produto->appendChild(
                    $doc->createTextNode(Mage::getBaseUrl() . $produtox->getUrlPath() . $product_suffix_url)
                );
                $produto->appendChild($link_produto);

                $imagem = $doc->createElement('oferta_imgproduto');
                $imageUrl = $produtox->getThumbnail() ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $produtox->getThumbnail() : '';
                $imagem->appendChild(
                    $doc->createTextNode($imageUrl)
                );
                $produto->appendChild($imagem);

                $principioativo = $doc->createElement('oferta_principio_ativo');
                $principioativo->appendChild(
                		$doc->createTextNode($produtox->getComposicaoNew())
                );
                $produto->appendChild($principioativo);
                
                $ministerio = $doc->createElement('oferta_codigo_ms');
                $ministerio->appendChild(
                		$doc->createTextNode($produtox->getNrMinisterioDaSaude())
                );
                $produto->appendChild($ministerio);
                
                $codigobarra = $doc->createElement('oferta_codigo_barra');
                $codigobarra->appendChild(
                		$doc->createTextNode('')
                );
                $produto->appendChild($codigobarra);
                
                $root->appendChild($produto);
              
            }
        }

        $path_to_save_xml = Mage::getBaseDir().'/cliquefarma/';
        
        if(is_dir($path_to_save_xml)){
            $doc->save($path_to_save_xml.'cliquefarma.xml');
        }
        else {
            mkdir($path_to_save_xml, 0777);
            $doc->save($path_to_save_xml.'cliquefarma.xml');
        }
        
        echo 'ok';
        
        exit;
    } // eof sendProductTotradepar
}