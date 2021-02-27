<?php
class Onestic_Smartpbm_Model_Observer {
    
    public function changeprice($observer) {
        $event = $observer->getEvent();
        $quote_item = $event->getQuoteItem();
        $product = Mage::getModel('catalog/product')->load($quote_item->getProduct()->getId());
        if ($product->getCodigoBarras()) {
            $pbmItem = Mage::getModel('smartpbm/quote')->getCollection()
                            ->addFieldToFilter('status', Onestic_Smartpbm_Model_Resource_Quote::QUOTE_STATUS_PENDING)
                            ->addFieldToFilter('product_id', $product->getId())
                            ->addFieldToFilter('quote_id', $quote_item->getQuoteId())
                            ->getFirstItem();

            if ($pbmItem->getId()) {
                $pbmItem = Mage::getModel('smartpbm/quote')->load($pbmItem->getId());
                $quote_item->setOriginalCustomPrice($pbmItem->getFinalprice());
                $quote_item->getProduct()->setIsSuperMode(true);

                $pbmItem->setStatus(Onestic_Smartpbm_Model_Resource_Quote::QUOTE_STATUS_ADDED);
                $pbmItem->save();
            }
        }
    }
    
    public function success($observer) {
        $order = $observer->getEvent()->getData('order');

        if (!$order) {
            $order_id = $observer->getEvent()->getData('order_ids');
            $order = Mage::getModel('sales/order')->load($order_id);
        }

        $quoteItems = Mage::getResourceModel('smartpbm/quote')->getItems($order->getQuoteId());
        $customer = $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname();
        foreach ($quoteItems as $item) {
            $orderData = array(
                'order_id'          => $order->getId(),
                'order_increment_id'=> $order->getIncrementId(),
                'pbm'               => $item['pbm'],
                'card'              => $item['card'],
                'customer'          => $customer,
                'ean'               => $item['ean'],
                'product_name'      => $item['product_name'],
                'product_id'        => $item['product_id'],
                'discount'          => $item['discount'],
                'original_price'    => $item['original_price'],
                'finalprice'        => $item['finalprice'],
                'qty'               => intval($item['qty']),
                'date'              => date('Y-m-d H:i:s'),
                'receipt'           => $item['receipt']
            );
            Mage::getResourceModel('smartpbm/order')->newOrder($orderData);
        }

        $orderPbms = Mage::getResourceModel('smartpbm/order')->getPbms($order->getId());
        $processedPbms = [];
        foreach ($orderPbms as $item) {
            if (!in_array($item['pbm'], $processedPbms)) {
                $pbm = Mage::getModel('smartpbm/pbms_' . $item['pbm']);
                $items = Mage::getModel('smartpbm/order')->getItemsByPbm($item['pbm'], $order_id);
                $pbm->preAutorizacao($items);
                $processedPbms[] = $item['pbm'];
            }
        }
    }
    
    public function confirmOrder($observer) {
        $order_id = $observer->getEvent()->getInvoice()->getOrder()->getId();
        $orderPbms = Mage::getResourceModel('smartpbm/order')->getPbms($order_id);
        foreach ($orderPbms as $item) {
            $pbm = Mage::getModel('smartpbm/pbms_' . $item['pbm']);
            $items = Mage::getModel('smartpbm/order')->getItemsByPbm($item['pbm'], $order_id);
            $pbm->confirmaBeneficio($items);
        }
    }
    
}
