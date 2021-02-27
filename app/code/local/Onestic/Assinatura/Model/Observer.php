<?php
class Onestic_Assinatura_Model_Observer {

    public function addToCart($observer) {
        $item = $observer->getEvent()->getQuoteItem();
        if($item){
            if ($is_recurring = Mage::app()->getRequest()->getParam('is_recurring')) {
                $quote = $item->getQuote();
                $quote->setIsRecurring(1);
                $quote->save();

                $product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
                $plan = Mage::app()->getRequest()->getParam('recurring_plan');
                //$planDiscount = $product->getData('assinatura' . $plan . '_desconto');
                //$planDiscount = 1 - ($planDiscount/100);
                //$price = $product->getPrice() * $planDiscount;
                $planPrice = $product->getData('assinatura' . $plan . '_valorfixo');
                $price = $planPrice;

                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
    }

    public function updateCart($observer) {

    }

    public function saveOrder($event) {
        $order_id = $event->getData('order_ids');
        $order = Mage::getModel('sales/order')->load($order_id);
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $order->setIsRecurring($quote->getIsRecurring());
        $order->save();
    }

}
