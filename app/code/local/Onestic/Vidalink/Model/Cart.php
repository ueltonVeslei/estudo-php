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
 * @package    Onestic_Vidalink
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_Vidalink_Model_Cart extends Varien_Object {
    
	public function estimatePost($postcode, $items ) {
		$quote = Mage::getModel('sales/quote')
        	->setStoreId(Mage::app()->getStore('default')->getId());
		$this->addItemsToQuote($quote, $items);
		$quote->getShippingAddress()
        	->setPostcode(substr('0'.$postcode,-8))
			->setCountryId("BR")
			->setCollectShippingRates(true);
		$quote->collectTotals();  
		return $this->getShippingMethod($quote);
	}
  
	public function getShippingMethod($quote) {
	    $result = 99999;
	    foreach($quote->getShippingAddress()->getGroupedAllShippingRates() as $carrier) {
	    	foreach($carrier as $method) {
	        	if ($result > $method->getPrice())
	        		$result = $method->getPrice();
			}
		}
		return $result;
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