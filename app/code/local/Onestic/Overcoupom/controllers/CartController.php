<?php

require_once 'Mage/Checkout/controllers/CartController.php';

class Onestic_Overcoupom_CartController extends Mage_Checkout_CartController
{


       /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();


            $couponCode = $this->_getQuote()->getCouponCode();

            $couponcodes = array();
            for($i=1;$i<=100;$i++){
                $couponcodes[] = Mage::helper('onestic_overcoupom')->getConfig('cupom'.$i);
            }
            Mage::log('Array Codigos ' . var_export($couponcodes,true), null, 'cupons.log'); 

            $ruleId = $this->_getSession()->getQuote()->getAppliedRuleIds();
            $salesRule = Mage::getModel('salesrule/rule')->load($ruleId);
            

            if((in_array($couponCode, $couponcodes))&&($couponCode != "")){
                Mage::log('Utilizando couponCode ' . $couponCode, null, 'cupons.log');      

                foreach ($cart->getQuote()->getAllItems() as $quote) {
                        
                    $attributeValue = Mage::getModel('catalog/product')->load($quote->getProduct()->getId())->getPromocaoNestle();
                    $product = Mage::getModel('catalog/product')->load($quote->getProduct()->getId());
                    if($product) {
                        $valid_rule = $salesRule->getConditions()->validate($quote);

                        if($attributeValue && $valid_rule){
                            $productPrice = $quote->getProduct()->getPrice();
                            Mage::log('couponPostAction ' . $attributeValue, null, 'cupons.log');
                            Mage::log('couponPostAction ' . $productPrice, null, 'cupons.log');
                            $quote->setOriginalCustomPrice($productPrice);
                            $quote->save();
                        }
                    }
                }     
            }



            $cart->save();
            if (!$this->_getQuote()->validateMinimumAmount()) {
                $warning = Mage::getStoreConfig('sales/minimum_order/description');
                $cart->getCheckoutSession()->addNotice($warning);
            }
        }
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                $cart->getCheckoutSession()->addMessage($message);
            }
        }
        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);
        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }

     /**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */

    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();



        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

           $couponcodes = array();
            for($i=1;$i<=100;$i++){
                $couponcodes[] = Mage::helper('onestic_overcoupom')->getConfig('cupom'.$i);
            }
            Mage::log('Array Codigos ' . var_export($couponcodes,true), null, 'cupons.log'); 

            $couponCode = $this->_getQuote()->getCouponCode();
            

            if((in_array($couponCode, $couponcodes))&&($couponCode != "")){
                Mage::log('Utilizando couponCode ' . $couponCode, null, 'cupons.log');


                $ruleId = $this->_getSession()->getQuote()->getAppliedRuleIds();
                $salesRule = Mage::getModel('salesrule/rule')->load($ruleId);
                  

                foreach ($cart->getQuote()->getAllItems() as $quote) {
                        
                        $attributeValue = Mage::getModel('catalog/product')->load($quote->getProduct()->getId())->getPromocaoNestle();
                        $product = Mage::getModel('catalog/product')->load($quote->getProduct()->getId());
                        if ($product) {
                            $valid_rule = $salesRule->getConditions()->validate($quote);

                            if($attributeValue && $valid_rule){
                                $productPrice = $quote->getProduct()->getPrice();
                                Mage::log('couponPostAction ' . $attributeValue, null, 'cupons.log');
                                Mage::log('couponPostAction ' . $productPrice, null, 'cupons.log');
                                $quote->setOriginalCustomPrice($productPrice);
                                $quote->save();
                            }
                        }
                    }     
                
            }



            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }

    /**
     * Delete shoping cart item action
     */
    public function deleteAction()
    {
        if ($this->_validateFormKey()) {
            $id = (int)$this->getRequest()->getParam('id');
            if ($id) {
                try {
                    $this->_getCart()->removeItem($id)
                        ->save();
                } catch (Exception $e) {
                    $this->_getSession()->addError($this->__('Cannot remove the item.'));
                    Mage::logException($e);
                }
            }
        } else {
            $this->_getSession()->addError($this->__('Cannot remove the item.'));
        }

        $this->_redirectReferer(Mage::getUrl('*/*'));
    }


    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

     

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
            $this->_goBack();
        }
        $this->_redirect('*/*');
    }


    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */

        $cart   = $this->_getCart();  

        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = strtoupper((string) $this->getRequest()->getParam('coupon_code'));

        //$ruleId = $this->_getSession()->getQuote()->getAppliedRuleIds();
        //$salesRule = Mage::getModel('salesrule/rule')->load($ruleId);
        
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
             foreach ($cart->getQuote()->getAllItems() as $quote) {
                    
                    $attributeValue = Mage::getModel('catalog/product')->load($quote->getProduct()->getId())->getPromocaoNestle();
                    $finalprice = Mage::getModel('catalog/product')->load($quote->getProduct()->getId())->getFinalPrice();
                    
                    //$valid_rule = $salesRule->getConditions()->validate($product);

                    if($attributeValue){
                        Mage::log('finalprice ' . $finalprice, null, 'cupons.log');
                        //$productPrice = $quote->getProduct()->getFinalPrice();
                        //Mage::log('couponPostAction ' . $attributeValue, null, 'cupons.log');
                        //Mage::log('couponPostAction ' . $productPrice, null, 'cupons.log');
                        $quote->setOriginalCustomPrice($finalprice);
                        $quote->save();
                    }
                }             
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        $couponcodes = array();
        for($i=1;$i<=100;$i++){
            $couponcodes[] = Mage::helper('onestic_overcoupom')->getConfig('cupom'.$i);
        }
        Mage::log('Array Codigos ' . var_export($couponcodes,true), null, 'cupons.log'); 

        

        if((in_array($couponCode, $couponcodes))&&($couponCode != "")){
            Mage::log('Utilizando couponCode ' . $couponCode, null, 'cupons.log');


            $ruleId = $this->_getSession()->getQuote()->getAppliedRuleIds();
            $salesRule = Mage::getModel('salesrule/rule')->load($ruleId);

            foreach ($cart->getQuote()->getAllItems() as $quote) {
                $attributeValue = Mage::getModel('catalog/product')->load($quote->getProduct()->getId())->getPromocaoNestle();
                $product = Mage::getModel('catalog/product')->load($quote->getProduct()->getId());
                /** CORREÇÃO DE ERRO PEGO PELO NEWRELIC **/
                if ($product) {
                    $valid_rule = $salesRule->getConditions()->validate($quote);

                    if($attributeValue && $valid_rule){
                        $productPrice = $quote->getProduct()->getPrice();
                        Mage::log('couponPostAction ' . $attributeValue, null, 'cupons.log');
                        Mage::log('couponPostAction ' . $productPrice, null, 'cupons.log');
                        $quote->setOriginalCustomPrice($productPrice);
                        $quote->save();
                    }
                }
            }     
            
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                    $this->_getSession()->setCartCouponCode($couponCode);
                } else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $appliedRuleIds = $this->_getSession()->getQuote()->getAppliedRuleIds();
        Mage::log('Utilizando cupom ' . $appliedRuleIds, null, 'cupons.log');

        $this->_goBack(); 
    }
}