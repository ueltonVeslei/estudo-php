<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Integrator
 * @package    Onestic_ApiServer
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_ApiServer_Model_Cart extends Varien_Object {
    
	public function estimatePost($requisicao) {
      Mage::helper('onestic_apiserver')->log('CONSULTAR PRODUTOS - estimatePost: ' . var_export($requisicao,true));
        $quote = Mage::getModel('sales/quote')
           ->setStoreId(Mage::app()->getStore('default')->getId());
            
        $peso = $requisicao->PesoTotal / 1000;
        $peso = str_replace(',', '.', $peso);
        $preco = str_replace(',', '.', $requisicao->ValorTotal);
        $cep = $requisicao->CEP;

        //$product = Mage::getModel('catalog/product')->loadByAttribute('sku','produto_frete_roche');
        //$product = Mage::getModel('catalog/product')->load($product->getID());
        
        //$product->setWeight($peso);
        //if($preco > 0){
        //    $product->setPrice($preco);
        //}
        //$product->save();
        //$product = Mage::getModel('catalog/product')->loadByAttribute('sku','produto_frete_roche');
        //$product = Mage::getModel('catalog/product')->load($product->getID());        
        
        //$buyInfo = array('qty' =>  1);
        //$quote->addProduct($product, new Varien_Object($buyInfo));

        
        //$this->addItemsToQuote($quote, $items);
        //$items = $quote->getItems();
        //$prod = Mage::getModel('catalog/product');
        
        
        //$item = $quote->getQuoteItem();

        //$quote->getShippingAddress()
        //    ->setPostcode($requisicao->CEP)
        //    ->setCountryId("BR")
        //    ->setCollectShippingRates(true);
        //$quote->collectTotals();  
        //$quote->save();

        //Mage::helper('onestic_apiserver')->log('CONSULTAR PRODUTOS - quote getId: ' . var_export($quote->getId(),true));

       // Mage::helper('onestic_apiserver')->log('CONSULTAR PRODUTOS - estimatePost (cotacao): ' . var_export($this->getShippingMethod($quote),true));

        $requestData = array(
            "origin_zip_code"           => Mage::getStoreConfig ('intelipost_basic/settings/zipcode'),
            "destination_zip_code"      => $cep,
            "quoting_mode"              => "DYNAMIC_BOX_ALL_ITEMS",
            "products"                  => array((object)array(
                                            "weight"            => $peso,
                                            "cost_of_goods"     => $preco,
                                            "width"             => 16,
                                            "height"            => 11,
                                            "length"            => 11,
                                            "quantity"          => 1,
                                            "sku_id"            => 33642,
                                            "product_category"  => Mage::getStoreConfig ('intelipost_basic/product_attributes/categories')
                                        )),
            "additional_information"    => (object)array(
                                            "lead_time_business_days"   => 0,
                                            "sales_channel"             => "Admin",
                                        ),
            "identification"            => (object)array(
                                            "url" => "https://www.farmadelivery.com.br/checkout/cart/"
                                        )
        );

        Mage::helper('basic')->setVersionControlData(Mage::helper('quote')->getModuleName(), 'quote');
        $api = Mage::getModel('basic/intelipost_api');
        $api->apiRequest(Intelipost_Basic_Model_Intelipost_Api::POST, "quote_by_product", $requestData, Mage::helper('basic')->getVersionControlModel());
        $methods = $api->apiResponseToObject();

        $result = array();
        foreach ($methods->content->delivery_options as $option) {
            $result[] = array(
                'Codigo'        => 'intelipost_' . $option->delivery_method_id,
                'Descricao'     => $option->description . ' - Estimativa ' . $option->delivery_estimate_business_days . ' dia(s) úteis',
                'Valor'         => $option->final_shipping_cost,
                'Observacao'    => 'Formas de Envio'
            );
        }        

        Mage::helper('onestic_apiserver')->log('CONSULTAR PRODUTOS - getShippingMethod: ' . var_export($result,true));

       $object = new stdClass();
       $object->complexObjectArray = $result;

        return $object; //$this->getShippingMethod($quote);
    }
  
	public function getShippingMethod($quote) {
        $result = array();
        foreach($quote->getShippingAddress()->getGroupedAllShippingRates() as $carrier) {
            foreach($carrier as $method) {
                $result[] = array('Codigo' => $method->getCode(), 'Descricao' => $method->getData('method_title'), 'Valor' => $method->getPrice(), 'Observacao' => $method->getData('carrier_title'));
                Mage::helper('onestic_apiserver')->log('CONSULTAR PRODUTOS - method: ' . var_export($method->getData(),true));
            }
        }
        Mage::helper('onestic_apiserver')->log('CONSULTAR PRODUTOS - getShippingMethod: ' . var_export($result,true));
       $object = new stdClass();
       $object->complexObjectArray = $result;
       return $object;
    }
  
	public function addItemsToQuote($quote, $items) {
		foreach($items as $item) {
	    	if ($item['status'] == 0) {
	        	$this->addItemToQuote($quote, $item);
	    	}
	    }
	}
  
	public function addItemToQuote($quote, $item) {
        $product = Mage::getModel('catalog/product')->loadByAttribute('codigo_barras',$item['EAN']);
        if ($product) {
	        $product = Mage::getModel('catalog/product')->load($product->getID());
	        $buyInfo = array('qty' =>  $item['Quantidade']);
	        return $quote->addProduct($product, new Varien_Object($buyInfo));
        }
	}
}