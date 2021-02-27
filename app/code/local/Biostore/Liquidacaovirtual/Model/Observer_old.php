<?php
class Flowecommerce_tradeparpricecompare_Model_Observer
{
    /**
    * This action will generate tradepar.xml to save to <website>/var/tradepar/
    * @return void
    */
    public function sendProductTotradepar()
    {
        $send_to_tradepar = Mage::getStoreConfig('catalog/tradeparpricecompare/send_to_tradepar');
        $tradepar_parcelomento = Mage::getStoreConfig('catalog/tradeparpricecompare/tradepar_parcelomento');

        // root element
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $root = $doc->createElement(Mage::app()->getStore()->getCode());
        $root = $doc->appendChild($root);
        if ($send_to_tradepar == 0) {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('enviar_para_tradepar', array('eq' => 1))
                ->addAttributeToFilter('status', 1);
        } else {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToFilter('status', 1);
        }

        if ($collection = $collection->getData()) {
            foreach ($collection as $cdata) {
                $produto = $doc->createElement('produto');
                $product_data = Mage::getModel('catalog/product')->load($cdata['entity_id']);
                $id_produto = $doc->createElement('id_produto');
                $id_produto->appendChild(
                    $doc->createTextNode($product_data->getSku())
                );
                $produto->appendChild($id_produto);

                $link_produto = $doc->createElement('link_produto');
                $link_produto->appendChild(
                    $doc->createTextNode($product_data->getProductUrl() . '?utm_medium=cpc&utm_source=Comparadores+de+Preco&utm_campaign=tradepar')
                );
                $produto->appendChild($link_produto);

                $titulo = $doc->createElement('titulo');
                $titulo->appendChild(
                    $doc->createTextNode($product_data->getName())
                );
                $produto->appendChild($titulo);

                $preco = $doc->createElement('preco');
                $preco->appendChild(
                    $doc->createTextNode(number_format($product_data->getPrice(),2))
                );
                $produto->appendChild($preco);

                $parcelamento = $doc->createElement('parcelamento');
                $parcelomento_value = '';
                $maximo = array();
                if ('bragspag' == $tradepar_parcelomento) {
                    $maximo = Mage::getModel('braspagcc/payment_gateway')->getParcelamentoMaximo($product_data);
                } else if ('parcelamento' == $tradepar_parcelomento) {
                    $maximo = Mage::getModel('parcelamento/parcelamento')->getParcelamentoMaximo($product_data);
                }

                $parcelomento_value = (!empty($maximo['parcelas']) && !empty($maximo['valor'])) ? "Até ".trim($maximo['parcelas'])." x de ".trim($maximo['valor']) : '';

                $parcelamento->appendChild(
                    $doc->createTextNode($parcelomento_value)
                );
                $produto->appendChild($parcelamento);

                $imagem = $doc->createElement('imagem');
                $imageUrl = $product_data->getImage() ? Mage::getUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $product_data->getImage() : '';
                $imagem->appendChild(
                    $doc->createTextNode($imageUrl)
                );
                $produto->appendChild($imagem);

                $categoria = $doc->createElement('categoria');
                $_categoryId = $product_data->getCategoryIds();
                if (count($_categoryId)> 0){
                    $_category = Mage::getModel('catalog/category')->load($_categoryId[0]);
                    $category_name = $_category->getName();
                }
                else {
                    $category_name = 'NA';
                }
                $categoria->appendChild(
                    $doc->createTextNode($category_name)
                );

                $produto->appendChild($categoria);

                $root->appendChild($produto);
            }
        }

        //header('Content-Type: text/xml');
        //echo $doc->saveXML();

        $path_to_save_xml = Mage::getBaseDir().'/tradepar/';
        if(is_dir($path_to_save_xml)){
            $doc->save($path_to_save_xml.'tradepar.xml');
        }
        else {
            mkdir($path_to_save_xml, 0777);
            $doc->save($path_to_save_xml.'tradepar.xml');
        }
        exit;
    } // eof sendProductTotradepar
}