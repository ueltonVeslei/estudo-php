<?php
class Controller_Order extends Controller {
	protected function _get() {
		if($orderID = $this->getData('ID')) {
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderID);

	        if ($order->getId()) {
	            $order = Mage::getModel('sales/order')->load($order->getId());
	            if (!$order->getDscViewed()) {
                    $this->setResponse('status',Standard::STATUS200);

                    $items = array();
                    foreach ($order->getAllItems() as $item) {
                        $image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item->getProductId(), 'image', Mage::app()->getStore()->getId());

                        if($image) {
                            $image = Mage::getModel('catalog/product_media_config')->getMediaUrl($image);
                        }

                        $productId    = $item->getProductId();
                        $product      = Mage::getModel('catalog/product')->load($productId);
                        $categoryIds  = $product->getCategoryIds();
                        $categoryName = '';

                        foreach ($categoryIds as $category_id) {
                            $category = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($category_id);
                            $categoryNames[] = $category->getName();
                        }

                        $categoriesNames = implode(', ',$categoryNames);

                        $items[] = array_merge($product->getData(), $item->getData(), [
                            'image'         => $image,
                            'categories_names' => $categoriesNames,
                            'categories_ids' => $categoryIds
                        ]);
                    }

                    $order->setDscViewed(1);
                    $order->save();

                    $this->setResponse('data', array_merge($order->getData(), [
                        'items' => $items,
                        'shipping' => $order->getShippingAddress()->getFormated(true),
                        'billing' => $order->getBillingAddress()->getFormated(true),
                        'delivery_name' => $order->getData()['shipping_description'],
                        'delivery_price' => $order->getData()['shipping_amount'],
                        'discount' => $order->getData()['discount_amount'],
                        'total' => $order->getData()['grand_total'],
                        'payment_method' => $this->getPaymentData($order),
                    ]));

                    return true;
                } else {
                    $this->setResponse('status',Standard::STATUS403);
                    $this->setResponse('data','Acesso negado');

                    return false;
                }
	        }
		}

		$this->setResponse('status',Standard::STATUS404);
		$this->setResponse('data','Dados não informados');
	}
	
	protected function _post() {}
	protected function _delete() {}

	protected function getPaymentData($order) {
        $payment = $order->getPayment();
        $additionalInfo = $payment->getAdditionalInformation();

        return [
            'additional_info' => $additionalInfo,
            'method' => $payment->getMethod(),
        ];
    }

}