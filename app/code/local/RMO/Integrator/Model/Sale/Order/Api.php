<?php

/**
 * @category   RMO
 * @package    RMO_Integrator
 * @author     Renato Marcelino <renato@skyhub.com.br>
 * @company    SkyHub
 * @copyright (c) 2013, SkyHub
 * 
 * 
 * SkyHub: Especialista em integrações para e-commerce.
 * Integramos sua loja Magento com os principais Marketplaces
 * e ERPs do mercado nacional. 
 * Para mais informações acesse: www.skyhub.com.br
 */

class RMO_Integrator_Model_Sale_Order_Api extends Mage_Sales_Model_Order_Api {

    const DEFAULT_SKYHUB_GROUP_CODE = "SKYHUB";
    
    public function estimateShipping($postCode, $items) {
        $result = Mage::getModel("rmointegrator/checkout_cart")->esitmatePost($postCode, $items);
        return $result;
    }
    
    public function listShipments($filters = null) {
     
        $collection = Mage::getResourceModel('sales/order_shipment_collection')
            ->addAttributeToSelect('increment_id')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('total_qty')
            ->join('sales/order', 'order_id=`sales/order`.entity_id', array('skyhub_code' => 'skyhub_code'), null, 'left');

        $preparedFilters = array();
        if (isset($filters->filter)) {
            foreach ($filters->filter as $_filter) {
                $preparedFilters[$_filter->key] = $_filter->value;
            }
        }
        if (isset($filters->complex_filter)) {
            foreach ($filters->complex_filter as $_filter) {
                $_value = $_filter->value;
                $preparedFilters[$_filter->key] = array(
                    $_value->key => $_value->value
                );
            }
        }

        if (!empty($preparedFilters)) {
            try {
                foreach ($preparedFilters as $field => $value) {
                    if (isset($this->_attributesMap['shipment'][$field])) {
                        $field = $this->_attributesMap['shipment'][$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        
        $result = array();

        foreach ($collection as $shipment) {
            $result[] = $this->_getAttributes($shipment, 'shipment');
        }

        return $result;
    }
    
    public function estimateShippingPerItem($postCode, $items) {
        $result = Mage::getModel("rmointegrator/checkout_cart")->estimatePostPerItem($postCode, $items);
        return $result;
    }
    
    public function invoice($skyhubCode, $newStatus) {
        $errors = array();
        try {    
            $order = Mage::getModel("sales/order")->loadByAttribute("skyhub_code", $skyhubCode);
            if (!$order->getId()) {
                $errors[] = "Não foi possível criar a fatura: Pedido(" . $skyhubCode.  ")   não encontrado";
                return $errors;
            }
            
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            
            if (count($newStatus) > 0) {
                $invoice->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $newStatus);
            }else {
                $invoice->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
            }

            $commentsObject = $order->getStatusHistoryCollection(true);
            foreach ($commentsObject as $commentObj) {
                $comentario = $commentObj->getComment();
                if (strpos($comentario, 'Skyhub') !== false) {
                    $comentario1 = explode("-", $comentario);
                    $comentario2 = explode(" ", $comentario1[0]);
                    $str = strtolower($comentario2[2]);
                    $meiopagamento = 'skyhub_' . $str;
                    $payment = $order->getPayment();
                    $payment->setMethod($meiopagamento);
                    $payment->save();
                    $order->save();
                }
            }

            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
        } catch (Exception $e) {
         $errors[]  = 'Error: ' . $e;
        }
        return $errors;
        
    }

    public function update($order) {
        $errors = array();
        try {
            $magentoOrder = Mage::getModel("sales/order")->loadByIncrementId($order->increment_id);
            if (!$magentoOrder->getId()) {
                $errors[] = "Pedido com increment id " . $order->increment_id . " não foi encontrado";
                return $errors;
            }

            if($order->skyhub_code){
              $magentoOrder->setSkyhubCode($order->skyhub_code);
              $magentoOrder->save();
            }

            if($order->billing_address){
              $billingAddress = $this->parseAddress($order->billing_address, $order->customer_email);
              $magentoOrder->getBillingAddress()->addData($billingAddress)->implodeStreetAddress()->save();
            }

            if($order->shipping_address){
              $shippingAddress = $this->parseAddress($order->shipping_address, $order->customer_email);
              $magentoOrder->getShippingAddress()->addData($shippingAddress)->implodeStreetAddress()->save();
            }

        } catch (Exception $e) {
            $errors[]  = 'Error: ' . $e;
        }
        return $errors;
    }

    protected function _setCurrentStoreCode($storeCode = null){
        $currentStoreCode = $this->_getCurrentStoreCode($storeCode);
        Mage::app()->setCurrentStore($currentStoreCode);
        return $currentStoreCode;
    }

    protected function _getCurrentStoreCode($storeCode = null){
        if($storeCode){
            $currentStoreCode = $storeCode;
        } else{
            $orderWebstoreConfig = Mage::getStoreConfig('rmointegrator/sale/order_webstore');
            if($orderWebstoreConfig){
                $currentStoreCode = $orderWebstoreConfig;
            }
        }
        return $currentStoreCode;
    }


    
    public function create($storeCode, $order) {
        Mage::helper('rmointegrator')->log('integratorOrderCreate: $storeCode = ' . $storeCode . ', $order->skyhub_code: = ' . $order->skyhub_code);
        $errors = array();
        try {    
            $orderExists = Mage::getModel("sales/order")->loadByAttribute("skyhub_code", $order->skyhub_code);
            if ($orderExists->getId()) {
                $errors[] = "Código de Pedido já importado";
                return $errors;
            }

            $customerGroup = Mage::getModel('customer/group')->load(self::DEFAULT_SKYHUB_GROUP_CODE, "customer_group_code");
            if (!$customerGroup->getId()) {
                $notLoggedInGroup = Mage::getModel('customer/group')->load(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                $customerGroup->setCode(self::DEFAULT_SKYHUB_GROUP_CODE);
                $customerGroup->setTaxClassId($notLoggedInGroup->getTaxClassId());
                $customerGroup->save();
            }

            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $current_store_id = Mage::app()->getStore($this->_getCurrentStoreCode($storeCode))->getId();
            $quote = Mage::getModel('sales/quote')->setStoreId($current_store_id);
            $quote->setIsSuperMode(true);
                        $quote->setReservedOrderId(Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($current_store_id));
            
            $quote->setSkyhubCode($order->skyhub_code);
            $this->prepareShippingMethod($order);
            $this->addItemsToQuote($quote, $order->items);
            $billingAddress = $this->parseAddress($order->billing_address, $order->customer_email);
            $shippingAddress = $this->parseAddress($order->shipping_address, $order->customer_email);
            
            $quote->getBillingAddress()
                    ->addData($billingAddress);

            $quote->getShippingAddress()
                    ->addData($shippingAddress)
                    ->setCollectShippingRates(true)
                    ->collectTotals()
                    ->setShippingMethod('skyhub_skyhub')
                    ->setPaymentMethod('skyhub_payment');

            $quote->setCheckoutMethod('guest')
                        ->setCustomerId(null)
                        ->setCustomerFirstname($order->customer_firstname)
                        ->setCustomerLastname($order->customer_lastname)
                        ->setCustomerTaxvat($order->billing_address->vat_number)
                        ->setCustomerCpf($order->billing_address->vat_number)
                        ->setCpf($order->billing_address->vat_number)
                        ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                        ->setCustomerIsGuest(true)

                        ->setCustomerGroupId($customerGroup->getId());
            $quote->getPayment()->importData( array('method' => 'skyhub_payment'));

            $quote->save();
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            if (count($order->status) > 0) {
                $mage_order = $service->getOrder();
                $mage_order->addStatusHistoryComment('Skyhub code: ' . $order->skyhub_code, $order->status);
                $mage_order->save();	
             }
            
        } catch (Exception $e) {
            $errors[]  = 'Error: ' . $e;
        }
        return $errors;
    }
    
    
    public function prepareShippingMethod($orderData){
        $shippingConfiguration = Mage::getSingleton("rmointegrator/sale_shipping_configuration");
        $shippingConfiguration->setIsActive(true);
        $shippingConfiguration->setShippingMethodName($orderData->shipping_description);
        $shippingConfiguration->setShippingMethodCode("skyhub");
        $shippingConfiguration->setShippingPrice($orderData->shipping_amount);
        
    }
    
    public function addItemsToQuote($quote, $itemsData) {
        $itemsArray = (array)$itemsData;
        foreach($itemsArray as $itemEntry) {
            if (is_array($itemEntry)) {
               $item = $itemEntry[0];
            }else {
               $item = $itemEntry;
            }
            $id = Mage::getModel('catalog/product')->getIdBySku($item->sku);
            $product = Mage::getModel('catalog/product')->load($id);
            $product['price'] = $item->original_price;
            $product['special_price'] = $item->special_price;
            $buyInfo = array('qty' => $item->qty);
            $quoteItem = $quote->addProduct($product, new Varien_Object($buyInfo));
        }
    }
    
    public function parseAddress($addressData, $customerEmail) {
        $directory = Mage::getModel("directory/region")->loadByCode($addressData->region, $addressData->country_id);
        if(!$addressData->secondary_phone){
            $addressData->secondary_phone = $addressData->telephone;
        }
        $address = array(
            'firstname' => $addressData->firstname,
            'lastname'  => $addressData->lastname,
            'company'   => $addressData->company,
            'email'     =>  $customerEmail,
            'street' => array(
                $addressData->street,
                $addressData->number,
                $addressData->complement,
                $addressData->neighborhood
            ),
            'complemento' => $addressData->complement,
            'bairro' => $addressData->neighborhood,
            'number' => $addressData->number,
            'city' => $addressData->city,
            'region_id' => $directory->getRegionId(),
            'region' => $addressData->region,
            'postcode' => $addressData->postcode,
            'country_id' => $addressData->country_id,
            'telephone' =>  $addressData->telephone,
            'number'    => $addressData->secondary_phone,
            'celular'   => $addressData->secondary_phone,
            'fax' => $addressData->fax,
            'customer_password' => '',
            'vat_id' => $addressData->vat_number,
            'cpf' => $addressData->vat_number,
            'confirm_password' =>  '',
            'save_in_address_book' => '0',
            'use_for_shipping' => '1',
            ''
        );
        return $address;
    }
    
    public function paymentMethods() {
       $payments = Mage::getSingleton('payment/config')->getAllMethods();
       $methods = array();
       foreach ($payments as $paymentCode=>$paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            $methods[] = array( 'key' => $paymentCode, 'value' => $paymentTitle);
        }
        return $methods;
    }
    
    public function shippingMethods() {
       $methods = Mage::getSingleton('shipping/config')->getAllCarriers();
        $shipMethods = array();
        foreach ($methods as $shippigCode=>$shippingModel) {
            $shippingTitle = Mage::getStoreConfig('carriers/'.$shippigCode.'/title');
            $shipMethods[] = array( 'key' => $shippigCode, 'value' => $shippingTitle);
        }
        return $shipMethods;
    }
    
    public function info($orderIncrementId) {
        $order_data = parent::info($orderIncrementId);
        $order  = $this->_initOrder($orderIncrementId);
        $order_data["billing_address"]["vat_number"] = $this->_findVatNumber($order);
        $order_data["customer_dob"] = $this->findCustomerDob($order);
        $order_data["customer_gender"] = $this->findCustomerGender($order);
        return $order_data;
    }
    
  public function listStatus(){
        $collection = Mage::getResourceModel('sales/order_status_collection');
        if($collection == null) {
            return $this->listStatusMagento14();
        }
        $collection->joinStates();
        $result = array();
        foreach($collection as $status) {
           $result[] = array( 'status' => $status->getStatus(), 'status_label' => $status->getLabel(), 'state' => $status->getState());
        }
        return $result;
    }
    
    public function listStatusMagento14() {
        $result = array();
        $result[] = array( 'status' => "pending", 'status_label' => "Pending", 'state' => "new");
        $result[] = array( 'status' => "pending_payment", 'status_label' => "Pending Payment", 'state' => "pending_payment");
        $result[] = array( 'status' => "processing", 'status_label' => "Processing", 'state' => "processing");
        $result[] = array( 'status' => "holded", 'status_label' => "On Hold", 'state' => "holded");
        $result[] = array( 'status' => "complete", 'status_label' => "Complete", 'state' => "complete");
        $result[] = array( 'status' => "closed", 'status_label' => "Closed", 'state' => "closed");
        $result[] = array( 'status' => "canceled", 'status_label' => "Canceled", 'state' => "canceled");
        $result[] = array( 'status' => "fraud", 'status_label' => "Suspected Fraud", 'state' => "payment_review");
        $result[] = array( 'status' => "payment_review", 'status_label' => "Payment Review", 'state' => "payment_review");
        return $result;
    }
    
    
    protected  function findCustomerDob($order){
        $customer_dob = $order->getData('customer_dob');
        /* WARNING: In the tests I did, the date always came in the format 1986-04-17 00:00:00.
         *  But I am not yet that it will came always in this format.
         */
        if($customer_dob != null && strlen($customer_dob)) {
            $onlyDate = substr ( $customer_dob , 0 , 10 );
            $parts = explode('-', $onlyDate);
            $customer_dob = $parts[2] . '-' . $parts[1] . '-' . $parts[0]; 
        }
        return $customer_dob;
    }
    
    protected  function findCustomerGender($order){
        if ($order->getCustomerGender() == 1) {
            return "male";
        } else if ($order->getCustomerGender() == 2) {
            return "female";
        }
        return "";
    }
    
    protected function _findVatNumber($order) {
        $customer_taxvat = $order->getData('customer_taxvat');
        if($customer_taxvat != null && strlen($customer_taxvat) > 0) {
            return $customer_taxvat;
        } else {
            return $order->getBillingAddress()->getData('vat_id');
        }
    }
}
