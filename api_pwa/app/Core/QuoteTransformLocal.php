<?php
abstract class  QuoteTransformLocal {

	public static function transform ($quote) {
    $quoteData = json_decode(json_encode($quote->getQuoteData()), true);

    $quoteDiscount = $quoteData['totals']['discount'];
    $quoteShipping = $quoteData['totals']['shipping'];
    $quoteTotal = $quoteData['totals']['total'];

    if ($quoteShipping > 0 && $quoteDiscount > 0) {
      $quoteData['totals']['discount'] = $quoteDiscount - $quoteShipping;

      $quoteData['totals']['total'] = $quoteTotal + $quoteShipping;
    }

    $descontoTotal = 0;
    foreach ($quoteData['items'] as $key => $item) {
      $descontoTotal += $item['discount_amount'];
    }
    $quoteData['totals']['discount'] = $descontoTotal;

    foreach ($quoteData['items'] as $key => $item) {
      $image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item['product']['product_id'], 'image', Mage::app()->getStore()->getId());

      if($image) {
        $item['product']['image'] = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
      }

      $quoteData['items'][$key] = $item;
    
    }

    return $quoteData;
  }
}

