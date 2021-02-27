<?php

class RMO_Integrator_Model_Checkout_Cart extends Varien_Object {
    
 public function estimatePostPerItem($postcode, $items) {
     $result = array();
     foreach($items as $item) {
         $quote = Mage::getModel('sales/quote')
                ->setStoreId(Mage::app()->getStore('default')->getId());
         $quoteItem = $this->addItemToQuote($quote, $item);
         if ($quoteItem->getProductId()) {
            $quote->getShippingAddress()
                ->setPostcode($postcode)
                ->setCountryId("BR")
                ->setCollectShippingRates(true);
            $quote->collectTotals();
            $result[] = array( 'item'=> $item->sku, 'shipping_info' => $this->parseShippingMethods($quote)  );
         }
    }     
    return $result;
 }   
    
  public function esitmatePost($postcode, $items ) {
      $quote = Mage::getModel('sales/quote')
                ->setStoreId(Mage::app()->getStore('default')->getId());
      $this->addItemsToQuote($quote, $items);
      $quote->getShippingAddress()
            ->setPostcode($postcode)
            ->setCountryId("BR")
            ->setCollectShippingRates(true);
      $quote->collectTotals();  
      return $this->parseShippingMethods($quote);
  }
  
  public function parseShippingMethods($quote) {
      $result = array();
      foreach($quote->getShippingAddress()->getGroupedAllShippingRates() as $carrier) {
          foreach($carrier as $method) {
              $methodTitle = $method->getCarrierTitle() . ' ' .   $method->getMethodTitle();
              $estimatedDelivery = $this->extractEstimatedDelivery($methodTitle);
              if ($estimatedDelivery != null ) { 
                $result[] = array( 'id' => $method->getCode(),  'title' => $methodTitle, 'price' => $method->getPrice(),  'estimated_delivery' => $estimatedDelivery );
              }
          }
      }
      return $result;
  }
  
  public function extractEstimatedDelivery($methodTitle) {
      preg_match('/\d+ dia/', $methodTitle, $matches);
      if (count($matches) > 0 ) {
        preg_match('/^\d+/', $matches[0], $matches);
        return $matches[0];
      } else {
          return null;
      }
  }
    
  
  public function addItemsToQuote($quote, $items) {
    foreach($items as $item) {
        $this->addItemToQuote($quote, $item);
    }
  }
  
   public function addItemToQuote($quote, $item) {
        $id = Mage::getModel('catalog/product')->getIdBySku( $item->sku);
        $product = Mage::getModel('catalog/product')->load($id);
        $buyInfo = array('qty' =>  $item->qty);
        return $quote->addProduct($product, new Varien_Object($buyInfo));
   }
}