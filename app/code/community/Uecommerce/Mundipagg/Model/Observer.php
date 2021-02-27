<?php

class Uecommerce_Mundipagg_Model_Observer extends Uecommerce_Mundipagg_Model_Standard
{
    private static $recurrence_payment_active = null;
    public function __construct($Store = null)
    {
        parent::__construct($Store);
        if (self::$recurrence_payment_active === null) {
            self::$recurrence_payment_active = Mage::getStoreConfig('payment/mundipagg_recurrencepayment/active');
        }
    }

    public function isRecurrencePaymentActive()
    {
        return self::$recurrence_payment_active == 1;
    }

    /*
	 * Update status and notify customer or not
	 */
    private function _updateStatus($order, $state, $status, $comment, $notified)
    {

        try {
            $order->setState($state, $status, $comment, $notified);
            $order->save();

            return $this;
        } catch (Exception $e) {
            //Api
            $api = Mage::getModel('mundipagg/api');

            //Log error
            Mage::logException($e);

            //Mail error
            $api->mailError(print_r($e->getMessage(), 1));
        }
    }

    public function checkCvv($event)
    {
        $valid = true;

        $order = $event->getOrder();
        $payment = $order->getPayment();
        $paymentInfo = $payment->getAdditionalInformation();

        foreach ($paymentInfo as $key => $value) {
            if (strpos($key, '_cvv') || strpos($key, '_cid')) {
                $securityCodes[] = $key;
            }
        }

        foreach ($securityCodes as $securityCode) {
            if (empty($paymentInfo[$securityCode])) {
                $valid = false;
            }
        }

        if (!$valid) {
            $helper = Mage::helper('mundipagg');

            $error = $helper->__("Invalid security code");
            Mage::throwException($error);
        }
    }

    public function canceledOrder($event)
    {
        $order = $event->getOrder();
        $state = $order->getState();

        if ($state == Mage_Sales_Model_Order::STATE_CANCELED) {
            //cancel Mundi transactions via API
            $this->cancelOrderViaApi($order);
        }
    }

    private function cancelOrderViaApi(Mage_Sales_Model_Order $order)
    {
        $standard = new Uecommerce_Mundipagg_Model_Standard($order->getStore());

        if ($standard->getCanceledByNotificationFlag($order)) {
            return;
        } else {
            unset($standard);
        }

        $payment = $order->getPayment();
        $paymentMethod = $payment->getAdditionalInformation('PaymentMethod');
        $allowedPaymentMethods = array(
            'mundipagg_creditcardoneinstallment',
            'mundipagg_creditcard',
            'mundipagg_twocreditcards'
        );

        if (!in_array($paymentMethod, $allowedPaymentMethods)) {
            return;
        }

        $logHelper = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
        $api = new Uecommerce_Mundipagg_Model_Api($order->getStore());
        $url = "{$this->getUrl()}/Cancel";

        $incrementId = $order->getIncrementId();
        $orderKeys = (array)$payment->getAdditionalInformation('OrderKey');

        foreach ($orderKeys as $orderKey) {
            $data = array('OrderKey' => $orderKey);

            $logHelper->info("Order #{$incrementId} | Order canceled. Cancel via MundiPagg Api...");
            $api->sendRequest($data, $url);
        }
    }

    /**
     * Update status
     * */
    public function updateStatus($event)
    {
        $method = $event->getOrder()->getPayment()->getAdditionalInformation('PaymentMethod');
        $approvalRequestSuccess = Mage::getSingleton('checkout/session')->getApprovalRequestSuccess();

        if ($method == 'mundipagg_boleto' && $approvalRequestSuccess != 'cancel') {
            $comment = Mage::helper('mundipagg')->__('MP - Waiting for Boleto Bancário payment');

            $event->getOrder()->hold();
            $event->getOrder()->addStatusHistoryComment($comment);
        }
    }

    /**
     * If were are not in a Mundipagg controller methods listed above we unset parcial
     */
    public function sessionUpdate($observer)
    {
        $action = $observer['controller_action']->getFullActionName();

        if (Mage::getStoreConfig('payment/mundipagg_standard/overwrite_magento_flags')) {
            if ($action != 'mundipagg_standard_redirect'
                && $action != 'mundipagg_standard_installments'
                && $action != 'mundipagg_standard_installmentsandinterest'
                && $action != 'mundipagg_standard_partial'
                && $action != 'mundipagg_standard_partialPost'
                && $action != 'mundipagg_standard_success'
            ) {
                Mage::getSingleton('checkout/session')->unsetData('approval_request_success');
                Mage::getSingleton('checkout/session')->unsetData('authorized_amount');
            }
        }
    }

    /**
     * Remove all non MundiPagg payment methods and MundiPagg Boleto from partial payment page
     */
    public function removePaymentMethods($observer)
    {
        $event = $observer->getEvent();
        $method = $event->getMethodInstance();
        $result = $event->getResult();
        $isPartial = Mage::getSingleton('checkout/session')->getApprovalRequestSuccess();

        if ($isPartial === 'partial') {
            switch ($method->getCode()) {
                case 'mundipagg_creditcardoneinstallment':
                case 'mundipagg_creditcard':
                    $active = Mage::getStoreConfig('payment/' . $method->getCode() . '/active');

                    if ($active == '1') {
                        $result->isAvailable = true;
                    } else {
                        $result->isAvailable = false;
                    }
                    break;
                case 'mundipagg_boleto':
                    $result->isAvailable = false;
                    break;
                default:
                    $result->isAvailable = false;
                    break;
            }
        }
    }

    public function removeInterest($observer)
    {
            /*$session = Mage::getSingleton('admin/session');

            if ($session->isLoggedIn()) {
                    $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
            } else {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
            }

            $quote->setMundipaggInterest(0.0);
            $quote->setMundipaggBaseInterest(0.0);
            $quote->setTotalsCollectedFlag(false)->collectTotals();
            $quote->save();*/
    }

    public function recurrenceMixConflict()
    {
        $session = Mage::getSingleton('checkout/session');

        if (!$session->getMundipaggRecurrency()) {
            return;
        }
        $recurrent = $session->getMundipaggRecurrency();
        if ($recurrent) {
            $quote = $session->getQuote();
            if ($this->checkRecurrenceMix($quote) &&
                $this->countTotalCartItems($quote) > 1
            ) {
                $msg = Mage::getStoreConfig('payment/mundipagg_recurrencepayment/conflict_message_recurrent_mix_mix');
                $message = Mage::getModel('core/message_warning', $msg);
                Mage::getSingleton('core/session')->addUniqueMessages($message);

                return;
            }
        }
    }

    private function countTotalCartItems($quote)
    {
        $items = $quote->getAllItems();
        $productQty = 0;
        foreach ($items as $item) {
            foreach ($item->getOptions() as $option) {
                $product = $option->getProduct();
                $product->load($product->getId());
                if ($item->getQty()) {
                    $productQty += $item->getQty();
                }
            }
        }
        return $productQty;
    }

    /**
    * Check if recurrency product is in cart in order to show
    * only Mundipagg Credit Card payment
    */
    public function checkForRecurrency($observer)
    {
        if (!$this->isRecurrencePaymentActive()) {
            return;
        }

        $session = Mage::getSingleton('checkout/session');

        $instance = $observer->getMethodInstance();
        $result = $observer->getResult();
        $code = $instance->getCode();

        if ($code === 'mundipagg_recurrencepayment') {
            $result->isAvailable = false;
        }
        $active = Mage::getStoreConfig('payment/' . $code . '/active');
        $thereIsrecurrentProduct = $this->checkRecurrence($session->getQuote());
        $isMundipagg = strpos($code, "mundipagg_");
        
        //0 => First string position = true.
        if ($isMundipagg === 0 &&
            $thereIsrecurrentProduct &&
            $active
        ) {
            $this->switchPaymentMethods($code, $result, $session->getQuote(), true);
        }
    }
    
    /**
     * Enable/disable Mundipagg payment methods for recurrence
     * @param string $code
     * @param object $result
     */
    private function switchPaymentMethods($code, $result, $quote)
    {
        $itemAlone = $this->checkItemAlone($quote);
        $recurenceMix = $this->checkRecurrenceMix($quote);
        
        if ($recurenceMix && $itemAlone) {
            $result->isAvailable = true;
            return;
        }
        
        switch ($code) {
            case 'mundipagg_boleto':
            case 'mundipagg_debit':
            case 'mundipagg_twocreditcards':
            case 'mundipagg_creditcardoneinstallment':
            case 'mundipagg_creditcard':
                if ($itemAlone && !$recurenceMix) {
                    $result->isAvailable = false;
                    break;
                }
                $result->isAvailable = true;
                break;
            case 'mundipagg_recurrencepayment':
                if ($itemAlone && !$recurenceMix) {
                    $result->isAvailable = true;
                    break;
                }
                $result->isAvailable = false;
                break;
            default:
                $result->isAvailable = false;
                break;
        }
    }

    /**
     * Check if exists a recurrent product in cart
     */
    private function checkRecurrence($quote)
    {
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            foreach ($item->getOptions() as $option) {
                $product = $option->getProduct();
                $product->load($product->getId());
                if ($product->getMundipaggRecurrent() === '1') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if exists a recurrence mix product in $items
     * @param array $items Cart items
     * @return boolean
     */
    public static function checkRecurrenceMix($quote)
    {
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            foreach ($item->getOptions() as $option) {
                $product = $option->getProduct();
                $product->load($product->getId());
                if ($product->getMundipaggRecurrenceMix()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if exists only on product in $items
     * @return boolean
     */
    public static function checkItemAlone($quote)
    {
        $items = $quote->getAllItems();
        if (count($items) > 1) {
            return false;
        }
        foreach ($items as $item) {
            foreach ($item->getOptions() as $option) {
                return $item->getQty() <= 1;
            }
        }
    }

    /*
     * Recurrent product needs to be alone.
     */
    public function cartCheckRecurrencyConflicts($observer)
    {
        if ($this->isRecurrencePaymentActive()) {
            $this->checkRecurrenceConflicts($observer);
        }
    }

    private function checkRecurrenceConflicts($observer)
    {
        $event = $observer->getEvent();
        
        if ($event->getQuoteItem()) {
            $quote = $event->getQuoteItem()->getQuote();
        } else {
            $quote = $event->getCart()->getQuote();
        }
        $items = $quote->getAllItems();
        $countItems = $this->countTotalCartItems($quote);

        if ($countItems > 0) {
            //Others + recurrent
            $this->showRecurrentFirstError($quote, $items[0]);
            $this->showOthersWithRecurrentError($items, $countItems);
        }
    }

    /**
     * Show an error when a recurrent product is adeed with others.
     */
    private function showOthersWithRecurrentError($items, $countItems)
    {
        if ($countItems >1 && !$this->checkRecurrentAlone($items)) {
            Mage::getSingleton('checkout/session')->addError(
                Mage::getStoreConfig('payment/mundipagg_recurrencepayment/conflict_message_recurrent_mix_recurrent')
            );
            Mage::getSingleton('checkout/session')->addError($this->__(''));
            return false;
        }
    }


    /**
     * Show an error when other products are added with a recurrent one;
     */
    private function showRecurrentFirstError($quote, $item)
    {
        if ($this->itemIsOnlyRecurrent($item) &&
            $this->countTotalCartItems($quote) > 1
        ) {
            Mage::getSingleton('checkout/session')->addError(
                Mage::getStoreConfig('payment/mundipagg_recurrencepayment/conflict_message_recurrent_others')
            );
            Mage::getSingleton('checkout/session')->addError($this->__(''));
            return false;
        }
    }

    private function itemIsOnlyRecurrent($item)
    {
        foreach ($item->getOptions() as $option) {
            $product = $option->getProduct();
            $product->load($product->getId());
            if ($product->getMundipaggRecurrent() &&
                !$product->getMundipaggRecurrenceMix()
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a recurrent product are alone in the cart
     * @param type $product
     */
    private function checkRecurrentAlone($items)
    {
        foreach ($items as $item) {
            foreach ($item->getOptions() as $option) {
                $product = $option->getProduct();
                $product->load($product->getId());
                if ($product->getMundipaggRecurrent() &&
                    !$product->getMundipaggRecurrenceMix()
                ) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Add discount amount in the quote when partial payment
     *
     * @param type $observer
     */
    public function addDiscountWhenPartial($observer)
    {
        $session = Mage::getSingleton('checkout/session');
        if (!$session->getApprovalRequestSuccess() == 'partial') {
            $request = Mage::app()->getRequest();
            if (Mage::app()->getRequest()->getActionName() != 'partialPost' &&
                $request->getModuleName() != 'mundipagg' &&
                $request->getControllerName() != 'standard'
            ) {
                return $this;
            }
        }
        $quote = $observer->getEvent()->getQuote();
        $quoteid = $quote->getId();

        $reservedOrderId = $quote->getReservedOrderId();

        if (!$reservedOrderId) {
            return $this;
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($reservedOrderId);

        if (!$order->getId()) {
            return $this;
        }

        $payment = $order->getPayment();

        $interestInformation = $payment->getAdditionalInformation('mundipagg_interest_information');
        $discountAmount = 0;

        if (isset($interestInformation)) {
            foreach ($interestInformation as $ii) {
                $discountAmount += (float) $ii->getValue();
            }
        }

        if ($quoteid) {
            $total = $quote->getBaseSubtotal();
            $quote->setSubtotal(0);
            $quote->setBaseSubtotal(0);

            $quote->setSubtotalWithDiscount(0);
            $quote->setBaseSubtotalWithDiscount(0);

            $quote->setGrandTotal(0);
            $quote->setBaseGrandTotal(0);

            $canAddItems = $quote->isVirtual() ? ('billing') : ('shipping');
            foreach ($quote->getAllAddresses() as $address) {
                $discountAmount -= $address->getShippingAmount();

                $address->setSubtotal(0);
                $address->setBaseSubtotal(0);

                $address->setGrandTotal(0);
                $address->setBaseGrandTotal(0);

                $address->collectTotals();

                $quote->setSubtotal((float) $quote->getSubtotal() + $address->getSubtotal());
                $quote->setBaseSubtotal((float) $quote->getBaseSubtotal() + $address->getBaseSubtotal());

                $quote->setSubtotalWithDiscount(
                    (float) $quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
                );
                $quote->setBaseSubtotalWithDiscount(
                    (float) $quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
                );

                $quote->setGrandTotal((float) $quote->getGrandTotal() + $address->getGrandTotal());
                $quote->setBaseGrandTotal((float) $quote->getBaseGrandTotal() + $address->getBaseGrandTotal());

                $quote->save();

                $quote->setGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                        ->setBaseGrandTotal($quote->getBaseSubtotal() - $discountAmount)
                        ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                        ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                        ->save();

                if ($address->getAddressType() == $canAddItems) {
                    //echo $address->setDiscountAmount; exit;
                    $address->setSubtotalWithDiscount((float) $address->getSubtotalWithDiscount() - $discountAmount);
                    $address->setGrandTotal((float) $address->getGrandTotal() - $discountAmount);
                    $address->setBaseSubtotalWithDiscount((float) $address->getBaseSubtotalWithDiscount() - $discountAmount);
                    $address->setBaseGrandTotal((float) $address->getBaseGrandTotal() - $discountAmount);
                    if ($address->getDiscountDescription()) {
                        $address->setDiscountAmount(-($address->getDiscountAmount() - $discountAmount));
                        $address->setDiscountDescription($address->getDiscountDescription() . ', Discount to Partial Payment');
                        $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount() - $discountAmount));
                    } else {
                        $address->setDiscountAmount(-($discountAmount));
                        $address->setDiscountDescription('Discount to Partial Payment');
                        $address->setBaseDiscountAmount(-($discountAmount));
                    }
                    $address->save();
                }
            }

            foreach ($quote->getAllItems() as $item) {
                //We apply discount amount based on the ratio between the GrandTotal and the RowTotal
                $rat = $item->getPriceInclTax() / $total;
                $ratdisc = $discountAmount * $rat;
                $item->setDiscountAmount(($item->getDiscountAmount() + $ratdisc) * $item->getQty());
                $item->setBaseDiscountAmount(($item->getBaseDiscountAmount() + $ratdisc) * $item->getQty())->save();
            }
        }
    }

    public function changeRecurrenceValues(Varien_Event_Observer $observer)
    {
        if (!$this->isRecurrencePaymentActive()) {
            return;
        }
        $quote = $observer->getCart()->getQuote();
        if (!$quote->getId()) {
            return;
        }

        foreach ($quote->getAllAddresses() as $address) {
            $address->setGrandTotal(0);
            $address->collectTotals();

            if ($this->checkRecurrenceMix($quote) &&
                $this->checkItemAlone($quote)
            ) {
                $canAddItems = $quote->isVirtual() ? ('billing') : ('shipping');
                if ($address->getAddressType() == $canAddItems) {
                    $discountAmount = $this->getRecurrenceDiscount($quote->getAllItems());
                    $address->setGrandTotal((float) $address->getGrandTotal() - $discountAmount);
                    $discountDescription = $address->getDiscountDescription();
                    if ($discountDescription) {
                        $address->setDiscountAmount(($address->getDiscountAmount() - $discountAmount));
                        $address->setDiscountDescription(
                            $discountDescription . ' + ' .
                            Mage::getStoreConfig('payment/mundipagg_recurrencepayment/recurrence_discount_message')
                        );
                    } else {
                        $address->setDiscountAmount(-($discountAmount));
                        $address->setDiscountDescription($msg);
                    }
                }
            }
        }
    }

    private function getRecurrencePartial($total, $items)
    {
        foreach ($items as $item) {
                $product = $item->getProduct();
                $recurrence = $product->getMundipaggRecurrent();
                $recurrences = $product->getMundipaggRecurrences();

            if ($recurrence === '1' && $recurrences > 1) {
                $frequency = $product->getMundipaggFrequencyEnum();

                switch ($frequency) {
                    case 'Quarterly':
                        $recurrences *= 3;
                        break;
                    case 'Biannual':
                        $recurrences *= 6;
                        break;
                    ;
                    case 'Yearly':
                        $recurrences *= 12;
                        break;
                }

                return $total / $recurrences;
            }
        }
    }

    private function getRecurrenceDiscount($items)
    {
        foreach ($items as $item) {
            $product = $item->getProduct();
            $discount = $product->getMundipaggRecurrenceDiscount();
            $price = $product->getPrice();
            if ($discount > 0) {
                $desconto = $price * ($discount/100);
                return $desconto;
            }
        }
    }

    public function catalogProductSaveBefore($event)
    {
        $product = $event->getProduct();
        $recurrentOption = (boolean)$product->getMundipaggRecurrent();

        if ($recurrentOption) {
                $isRequired = true;
        } else {
                $isRequired = false;
        }

        try {
                $attribute = new Mage_Eav_Model_Entity_Attribute();
                $attribute->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'mundipagg_recurrences');
                $attribute->setIsRequired($isRequired);
                $attribute->save();
        } catch (Mage_Adminhtml_Exception $e) {
                $log = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
                $helper = Mage::helper('mundipagg');

                $errMsg = "{$helper->__("Internal error")}: {$e->getMessage()}";
                $log->error($helper->__("Unable to save product configuration: {$e}"));

                throw new Mage_Adminhtml_Exception($errMsg);
        }
    }

    public function checkRecurrencyFrequency(Varien_Event_Observer $observer)
    {
            $product = $observer->getProduct();
            $recurrent = (boolean)$product->getData('mundipagg_recurrent');
            $frequency = $product->getData('mundipagg_frequency_enum');

        if ($recurrent === true && $frequency == '0') {
                $helper = Mage::helper('mundipagg');
                $errMsg = $helper->__('Recurrency frequency is required');

                Mage::throwException($errMsg);
        }
    }

    /**
     * @param Varien_Event $event
     */
    public function cartCheckRecurrency($event)
    {
        /* @var Mage_Checkout_Model_Cart $cart */
        $cart = $event->getCart();

        /* @var Mage_Sales_Model_Quote $quote */
        $quote = $cart->getQuote();

        /* @var Mage_Sales_Model_Resource_Quote_Item_Collection $items */
        $items = $quote->getAllItems();

        /* @var Mage_Sales_Model_Quote_Item $item */
        foreach ($items as $item) {
            /* @var Mage_Sales_Model_Quote_Item_Option $option */
            foreach ($item->getOptions() as $option) {
                    /* @var Mage_Catalog_Model_Product $product */
                    $product = $option->getProduct();
                    $product->load($product->getId());

                if ($product->getMundipaggRecurrent()) {
                        $this->setQuoteRecurrencyFlag(true);

                        return;
                }
            }
        }

        $this->setQuoteRecurrencyFlag(false);
    }

    /**
     * @param boolean $option
     *
     */
    private function setQuoteRecurrencyFlag($option)
    {
            $session = Mage::getSingleton('checkout/session');
            $session->setMundipaggRecurrency($option);
    }
        
    public function checkModuleVersion()
    {
        $localModuleVersion = $this->readModuleVersion();
        $repoVersion = $this->getRepoVersion();
        if (version_compare($localModuleVersion, $repoVersion, "<") == 1) {
            $this->insertOldVersionNotification($localModuleVersion, $repoVersion);
        }
    }

    private function insertOldVersionNotification($oldVersion, $newVersion)
    {
        $notification = mage::getModel("adminnotification/inbox");
        $data = array(
            'severity'=>Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR
            ,'title'=> 'Nova versão do módulo de integração Mundipagg disponível.'
            ,'description'=>
                "Você está utilizando uma versão antiga do módulo de interação Mundipagg(v" . $oldVersion . "). Atualize para a versão v" . $newVersion . "<br>
                <a href='https://www.magentocommerce.com/magento-connect/mundipagg-payment-gateway.html' target='_blank'>Download Magento Connect</a><br>
                <a href='https://github.com/mundipagg/magento-module' target='_blank'>GitHub</a>
                "
            ,'url' =>"https://www.magentocommerce.com/magento-connect/mundipagg-payment-gateway.html"
            ,'is_read' => 0
            ,'is_remove'=>0
            ,'date_added'=> now()
        );
        $notification->setData($data);
        $notification->save();
    }

    private function readModuleVersion()
    {
        $configXml = Mage::getBaseDir('app') . "/code/community/Uecommerce/Mundipagg/etc/config.xml";
        if (file_exists($configXml)) {
            $xmlObj = simplexml_load_file($configXml);
            return $xmlObj->modules->Uecommerce_Mundipagg->version[0];
        } else {
            return 0;
        }
    }

    private function getRepoVersion()
    {
        $url = "https://api.github.com/repos/mundipagg/magento-module/releases";
        $ch = curl_init();

        // Header
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mundipagg');
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Execute post
        $_response = curl_exec($ch);

        if (curl_errno($ch)) {
            $helperLog = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
            $helperLog->info(curl_error($ch));
        }
        $response = (json_decode($_response));
        return str_replace("v", "", $response[0]->tag_name);
    }

    /**
     * Force cart clean after order payment
     */
    public function cleanCart()
    {
        Mage::getSingleton('checkout/session')
            ->getQuote()
            ->setIsActive(false)
            ->save();

        Mage::getSingleton('checkout/session')->clear();
    }
}
