<?php

class Edrone_Base_Model_Observer {

    public function addToCart() {
        if (!$this->isEnabled()) {
            return;
        }
        $product = Mage::getModel('catalog/product')
                ->load(Mage::app()->getRequest()->getParam('product', 0));
        if (!$product->getId()) {
            return;
        }
        Mage::getModel('core/session')->setProductToShoppingCart(
                new Varien_Object(array(
            'sku' => $product->getSku(),
            'title' => $product->getName(),
            'image' => (string) Mage::helper('catalog/image')->init($product, 'image')->keepFrame(false)->resize(null, 438),
            'id' => $product->getId(),
                ))
        );
    }

    private function getOrderData($order) {
        if (!$this->isEnabled()) {
            return;
        }

        $orderData = array();
        $customerData = array();
        $product_category_names = array();
        $product_category_ids = array();
        $product_counts = array();
        $skus = array();
        $ids = array();
        $titles = array();
        $images = array();

        foreach ($order->getAllVisibleItems() as $item) {
            $_Product = $item->getProduct();
            $parent = $_Product->getParentItem();
            if ($parent) {
                $skus[] = $parent->getSku();
                $ids[] = $parent->getId();
                $titles[] = $parent->getName();
                $images[] = ($parent) ? (string) Mage::helper('catalog/image')->init($_Product, 'image')->keepFrame(false)->resize(null, 438) : '';
            } else {
                $skus[] = $_Product->getSku();
                $ids[] = $_Product->getId();
                $titles[] = $_Product->getName();
                $images[] = ($_Product) ? (string) Mage::helper('catalog/image')->init($_Product, 'image')->keepFrame(false)->resize(null, 438) : '';
            }
            $product_counts[] = (int) $item->getQtyOrdered();
            $categoryIds = $_Product->getCategoryIds(); //array of product categories
            $catNamesArray = array();

            if (!empty($categoryIds)) {
                $categories = Mage::getModel('catalog/category')
                        ->getCollection()
                        ->addAttributeToSelect("*")
                        ->addAttributeToFilter('entity_id', $categoryIds);

                foreach ($categories as $category) {
                    array_push($catNamesArray, $category->getName());
                }

                $product_category_names[] = implode("~", $catNamesArray);
                $product_category_ids[] = implode("~", $categoryIds);
            } else {
                $product_category_names[] = '';
                $product_category_ids[] = '';
            }
        }
        $orderData['sku'] = join('|', $skus);
        $orderData['id'] = join('|', $ids);
        $orderData['title'] = join('|', $titles);
        $orderData['image'] = join('|', $images);
        $orderData['order_id'] = $order->getIncrementId();
        $orderData['order_payment_value'] = $order->getGrandTotal();
        $orderData['base_payment_value'] = $order->getBaseGrandTotal();
        $orderData['base_currency'] = $order->getBaseCurrencyCode();
        $orderData['order_currency'] = $order->getOrderCurrencyCode();
        $orderData['coupon'] = $order->getCouponCode();
        $orderData['product_category_names'] = join('|', $product_category_names);
        $orderData['product_category_ids'] = join('|', $product_category_ids);
        $orderData['product_counts'] = join('|', $product_counts);

        $customerData['first_name'] = $order->getBillingAddress()->getFirstname();
        $customerData['last_name'] = $order->getBillingAddress()->getLastname();
        $customerData['email'] = $order->getBillingAddress()->getEmail();
        $customerData['country'] = $order->getBillingAddress()->getCountry();
        $customerData['city'] = $order->getBillingAddress()->getCity();
        $customerData['phone'] = $order->getBillingAddress()->getTelephone();

        return array($orderData, $customerData);
    }

    private function sendDataToServer($orderData, $customerData) {
        if (!$this->isEnabled()) {
            return;
        }
        try {
            $configHelper = Mage::helper('edrone/config');
            if (!$configHelper->isServerSideTraceEnabled()) {
                return;
            }
            $edrone = new Edrone_Base_Model_Utilities_EdroneIns($configHelper->getAppId(), '');
            $edrone->setCallbacks(function($obj) {
                Mage::log("EDRONEPHPSDK ERROR - wrong request:" . json_encode($obj->getLastRequest()));
            }, function() {

            });
            $edrone->prepare(
                    Edrone_Base_Model_Utilities_EdroneEventOrder::create()->
                            userFirstName(($customerData['first_name']))->
                            userLastName(($customerData['last_name']))->
                            userEmail($customerData['email'])->
                            productSkus($orderData['sku'])->
                            productTitles($orderData['title'])->
                            productImages($orderData['image'])->
                            productCategoryIds($orderData['product_category_ids'])->
                            productCategoryNames($orderData['product_category_names'])->
                            orderId($orderData['order_id'])->
                            orderPaymentValue($orderData['order_payment_value'])->
                            orderCurrency($orderData['order_currency'])->
                            platformVersion(Mage::getVersion())->
                            productCounts($orderData['product_counts'])
            )->send();
        } catch (Exception $e) {
            Mage::log("EDRONEPHPSDK ERROR:" . $e->getMessage() . ' more :' . json_encode($e));
        }
        return json_encode($edrone->getLastRequest());
    }

    public function export_new_order($observer) {
        if (!$this->isEnabled()) {
            return;
        }
        $order = $observer->getEvent()->getOrder();
        $orderdata = $this->getOrderData($order);
        $this->sendDataToServer($orderdata[0], $orderdata[1]);
        return $this;
    }

    public function edroneOrderCancel($observer) {

       if (!$this->isEnabled()) {
          return;
       }
       $configHelper = Mage::helper('edrone/config');
       if (!$configHelper->isServerSideTraceEnabled()) {
          return;
       }
       $order = $observer->getEvent()->getOrder();

       $orderData = array();
       $customerData = array();
       $orderData['order_id'] = $order->getIncrementId();
       $orderData['sender_type'] = 'server';
       $orderData['action_type'] = 'order_cancel';

       try {
           $edrone = new Edrone_Base_Model_Utilities_EdroneIns($configHelper->getAppId(), '');
           $edrone->setCallbacks(function($obj) {
               Mage::log("EDRONEPHPSDK ERROR - wrong request:" . json_encode($obj->getLastRequest()));
           }, function($obj) {

           });
           $edrone->prepare(
                   Edrone_Base_Model_Utilities_EdroneEventOrderCancel::create()->
                     userEmail($order->getBillingAddress()->getEmail())->
                     platformVersion(Mage::getVersion())->
                     orderId($order->getIncrementId())
           )->send();
       } catch (Exception $e) {
           Mage::log("EDRONEPHPSDK ERROR:" . $e->getMessage() . ' more :' . json_encode($e));
       }

       return $this;
    }

    public function newsletterSubscriberChange($observer) {
        if (!$this->isEnabled()) {
            return;
        }
        $configHelper = Mage::helper('edrone/config');
        if (!$configHelper->isServerSideTraceEnabled()) {
            return;
        }
        $subscriber = $observer->getEvent()->getSubscriber();

        $subscriberStatusForEdrone = null;
        if ($subscriber->isSubscribed()) {
            $subscriberStatusForEdrone = 1;
            Mage::getSingleton('core/session')->setEdroneSubscriberStatus($subscriberStatusForEdrone);
        } else {
            $subscriberStatusForEdrone = 0;
            Mage::getSingleton('core/session')->setEdroneSubscriberStatus($subscriberStatusForEdrone);
        }

        try {
            $edrone = new Edrone_Base_Model_Utilities_EdroneIns($configHelper->getAppId(), '');
            $edrone->setCallbacks(function($obj) {
                Mage::log("EDRONEPHPSDK ERROR - wrong request:" . json_encode($obj->getLastRequest()));
            }, function($obj) {

            });
            $edrone->prepare(
                    Edrone_Base_Model_Utilities_EdroneEventSubscribe::create()->
                            userEmail($subscriber->getEmail())->
                            platformVersion(Mage::getVersion())->
                            userSubscriberStatus($subscriberStatusForEdrone)
            )->send();
        } catch (Exception $e) {
            Mage::log("EDRONEPHPSDK ERROR:" . $e->getMessage() . ' more :' . json_encode($e));
        }
    }

    private function isEnabled() {
        $isEnabled = Mage::getStoreConfig('edrone/base/enable');
        return $isEnabled;
    }

}
