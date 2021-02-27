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
 * @package    Onestic_Skyhub
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_Skyhub_Model_Order extends Varien_Object {

    const DEFAULT_GROUP_CODE = "SKYHUB";

    protected $_regions = [
        ['AC','69900000','69999999'],
        ['AL','57000000','57999999'],
        ['AM','69000000','69299999'],
        ['AM','69400000','69899999'],
        ['AP','68900000','68999999'],
        ['BA','40000000','48999999'],
        ['CE','60000000','63999999'],
        ['DF','70000000','72799999'],
        ['DF','73000000','73699999'],
        ['ES','29000000','29999999'],
        ['GO','72800000','72999999'],
        ['GO','73700000','76799999'],
        ['MA','65000000','65999999'],
        ['MG','30000000','39999999'],
        ['MS','79000000','79999999'],
        ['MT','78000000','78899999'],
        ['PA','66000000','68899999'],
        ['PB','58000000','58999999'],
        ['PE','50000000','56999999'],
        ['PI','64000000','64999999'],
        ['PR','80000000','87999999'],
        ['RJ','20000000','28999999'],
        ['RN','59000000','59999999'],
        ['RO','76800000','76999999'],
        ['RR','69300000','69399999'],
        ['RS','90000000','99999999'],
        ['SC','88000000','89999999'],
        ['SE','49000000','49999999'],
        ['SP','01000000','19999999'],
        ['TO','77000000','77999999']
    ];
    
    public function create($order) {
    	Mage::log('IMPORTANDO PEDIDO: ' . $order->code,NULL,'onestic_skyhub.log');
        $exported = false;
        Mage::log(var_export($order, true),NULL, 'onestic_skyhub_json.log');
        if ($order->shipping_method == 'SkyHub - me2-Normal (fulfillment)' || $order->shipping_method == 'me2-Normal (fulfillment)' ||
            $order->shipping_method == 'SkyHub - me2-Expresso (fulfillment)' || $order->shipping_method == 'me2-Expresso (fulfillment)' ||
            $order->shipping_method == 'SkyHub - me2-Prioritário (fulfillment)' || $order->shipping_method == 'me2-Prioritário (fulfillment)') {

            Mage::log('PEDIDO FULLFILMENT: ' . $order->code,NULL,'onestic_skyhub_fullfilment.log');
            //Mage::getModel('onestic_skyhub/api_orders')->exported($order->code); 

            $exported = false;

            return true;

        }

        try {
            $orderExists = Mage::getModel("sales/order")->loadByAttribute("skyhub_code", $order->code);
            if ($orderExists->getId()) {
                Mage::log('PEDIDO JÁ REGISTRADO: ' . $order->code,NULL,'onestic_skyhub.log');
                return true;
            }
            
            $customerGroup = Mage::getModel('customer/group')->load(self::DEFAULT_GROUP_CODE, "customer_group_code");
            if (!$customerGroup->getId()) {
                $notLoggedInGroup = Mage::getModel('customer/group')->load(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                $customerGroup->setCode(self::DEFAULT_GROUP_CODE);
                $customerGroup->setTaxClassId($notLoggedInGroup->getTaxClassId());
                $customerGroup->save();
            }
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $current_store_id = Mage::app()->getStore('default')->getId();
            $quote = Mage::getModel('sales/quote')->setStoreId($current_store_id);
            $quote->setIsSuperMode(true);
            $this->prepareShippingMethod($order);
            $this->addItemsToQuote($quote, $order->items);
            if ($order->billing_address->full_name == 'Não informado') {
                $order->billing_address->full_name = $order->customer->name;
            }
            if ($order->shipping_address->full_name == 'Não informado') {
                $order->shipping_address->full_name = $order->customer->name;
            }

            $billingAddress = $this->parseAddress($order,'billing_address');
            $shippingAddress = $this->parseAddress($order,'shipping_address');
            $quote->getBillingAddress()
                    ->addData($billingAddress);
            
            $quote->getShippingAddress()
                    ->addData($shippingAddress)
                    ->setCollectShippingRates(true)
                    ->collectTotals()
                    ->setShippingMethod('onestic_entrega')
                    ->setPaymentMethod($this->paymentCode($order->channel));
            $customer = $order->customer;
            $space = strpos($customer->name,' ');
            $firstname = substr($customer->name, 0, $space);
            $lastname = substr($customer->name,$space);
            
            $quote->setCheckoutMethod('guest')
                ->setCustomerId(null)
                ->setCustomerFirstname($firstname)
                ->setCustomerLastname($lastname)
                ->setCustomerTaxvat($customer->vat_number)
                ->setCustomerCpf($customer->vat_number)
                ->setCpf($customer->vat_number)
                ->setCustomerEmail('mkt@emailskyhub.com.br')
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId($customerGroup->getId());
            $quote->getPayment()->importData( array('method' => $this->paymentCode($order->channel)));
            $quote->getPayment()->setAdditionalInformation('parcels',$order->payments[0]->parcels);

            $this->calculateInterest($order, $quote);

            $quote->save();

            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            $firstHifenPos = strpos(trim($order->code),'-');
            $marketplace = substr(trim($order->code), 0, $firstHifenPos);
            $mkID = substr(trim($order->code), $firstHifenPos+1);
            
            $mage_order = $service->getOrder();
            $mage_order->addStatusHistoryComment('Skyhub code: ' . $order->code);
            $mage_order->setMarketplace($marketplace);
            $mage_order->setMarketplaceId($mkID);
            $mage_order->setSkyhubCode($order->code);
            $mage_order->save();
            
            if($mage_order->getId()) {
	            if($order->status->code == 'aprovado') {
	                $this->_invoice($mage_order->getId());
	            } elseif ($order->status->code == 'cancelado') {
	                $this->_cancel($mage_order->getId());
	            }
	            Mage::log('PEDIDO SINCRONIZADO ' . $order->code . ' == ' . $mage_order->getIncrementId(),0,'onestic_skyhub.log');
	            //$mage_order->getResource()->updateGridRecords($mage_order->getId());
	            $exported = true;
            } else {
            	Mage::throwException('ERRO DESCONHECIDO');
            }
        } catch (Exception $e) {
            Mage::log('ERRO AO SINCRONIZAR PEDIDO ' . $order->code . ': ' . $e->getMessage(),0,'onestic_skyhub.log');
            $exported = false;
        }
        Mage::log('PEDIDO ' . $order->code . ' IMPORTADO',NULL,'onestic_skyhub.log');
        return $exported;
    }
    
    public function checkDiscount($order, $quote) {
        $discountAmount = floatval($order->discount);
        if($discountAmount > 0) {
            $total = $quote->getBaseSubtotal();
            $quote->setSubtotal(0);
            $quote->setBaseSubtotal(0);
        
            $quote->setSubtotalWithDiscount(0);
            $quote->setBaseSubtotalWithDiscount(0);
        
            $quote->setGrandTotal(0);
            $quote->setBaseGrandTotal(0);
        
            $canAddItems = $quote->isVirtual()? ('billing') : ('shipping');
            foreach ($quote->getAllAddresses() as $address) {
        
                $address->setSubtotal(0);
                $address->setBaseSubtotal(0);
        
                $address->setGrandTotal(0);
                $address->setBaseGrandTotal(0);
        
                $address->collectTotals();
        
                $quote->setSubtotal((float) $quote->getSubtotal() + $address->getSubtotal());
                $quote->setBaseSubtotal((float) $quote->getBaseSubtotal() + $address->getBaseSubtotal());
        
                $quote->setSubtotalWithDiscount(
                    (float)$quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
                );
                $quote->setBaseSubtotalWithDiscount(
                    (float)$quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
                );
        
                $quote->setGrandTotal((float) $quote->getGrandTotal() + $address->getGrandTotal());
                $quote->setBaseGrandTotal((float) $quote->getBaseGrandTotal() + $address->getBaseGrandTotal());
        
                $quote->save();
        
                $quote->setGrandTotal($quote->getBaseSubtotal()-$discountAmount)
                    ->setBaseGrandTotal($quote->getBaseSubtotal()-$discountAmount)
                    ->setSubtotalWithDiscount($quote->getBaseSubtotal()-$discountAmount)
                    ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal()-$discountAmount)
                    ->save();
        
        
                if($address->getAddressType() == $canAddItems) {
                    $address->setSubtotalWithDiscount((float) $address->getSubtotalWithDiscount()-$discountAmount);
                    $address->setGrandTotal((float) $address->getGrandTotal()-$discountAmount);
                    $address->setBaseSubtotalWithDiscount((float) $address->getBaseSubtotalWithDiscount()-$discountAmount);
                    $address->setBaseGrandTotal((float) $address->getBaseGrandTotal()-$discountAmount);
                    if($address->getDiscountDescription()){
                        $address->setDiscountAmount(-($address->getDiscountAmount()-$discountAmount));
                        $address->setDiscountDescription($address->getDiscountDescription().', Desconto');
                        $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount()-$discountAmount));
                    }else {
                        $address->setDiscountAmount(-($discountAmount));
                        $address->setDiscountDescription('Desconto');
                        $address->setBaseDiscountAmount(-($discountAmount));
                    }
                    $address->save();
                }
            }
        
            foreach($quote->getAllItems() as $item){
                $rat = $item->getPriceInclTax()/$total;
                $ratdisc = $discountAmount*$rat;
                $item->setDiscountAmount(($item->getDiscountAmount()+$ratdisc) * $item->getQty());
                $item->setBaseDiscountAmount(($item->getBaseDiscountAmount()+$ratdisc) * $item->getQty())->save();
            }
        }
    }

    public function calculateInterest($order, $quote){
        $interest = Mage::app()->getStore()->convertPrice($order->interest);
        
        $quote->setInterest((float) $interest);
        $quote->setBaseInterest((float) $interest);

        $quote->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();
    }
    
    public function prepareShippingMethod($order){
        $shippingConfiguration = Mage::getSingleton("onestic_skyhub/shipping_configuration");
        $shippingConfiguration->setIsActive(true);
        $shippingConfiguration->setShippingCarrierName($order->shipping_carrier);
        $shippingConfiguration->setShippingMethodName($order->shipping_method);
        $shippingConfiguration->setShippingMethodCode("entrega");
        $shippingConfiguration->setShippingPrice(number_format($order->shipping_cost,2));
    }
    
    public function addItemsToQuote($quote, $itemsData) {
        $itemsArray = (array)$itemsData;
        foreach($itemsArray as $itemEntry) {
            if (is_array($itemEntry)) {
                $item = $itemEntry[0];
            }else {
                $item = $itemEntry;
            }
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$item->id);
            if ($product) {
                $product = Mage::getModel('catalog/product')->load($product->getId());
                $product->setPrice($item->original_price);
                $product->setSpecialPrice($item->special_price);
                $buyInfo = array(
                    'qty'           => $item->qty,
                    'product_id'    => $product->getId()                    
                );
                $options = array();
                foreach ($product->getOptions() as $option) {
                    if ($option->getIsRequire()) {
                        $id = $option->getOptionId();
                        if (in_array($option->getType(),array('radio','drop_down','checkbox','multiple'))) {
                            $values = $option->getValues();
                            foreach ($values as $value) {
                                $options[$id] = $value->getOptionTypeId();
                                break;
                            }
                        } elseif (in_array($option->getType(),array('field','area'))) {
                            $options[$id] = 'VALOR PADRÃO';
                        } elseif (in_array($option->getType(),array('date','date_time','time'))) {
                            $options[$id] = date('Y-m-d');
                        } else {
                            $options[$id] = 'SEM ARQUIVO';
                        }
                    }
                }
                if($options) {
                    $buyInfo['options'] = $options;
                }
                $quote->addProduct($product, new Varien_Object($buyInfo));
            }
        }
    }

    protected function getRegionByPostcode($address) {
        $regionFound = '';

        foreach ($this->_regions as $region) {
            if ($address->postcode >= $region[1] && $address->postcode <= $region[2]) {
                $regionFound = $region[0];
                break;
            }
        }

        if (!$regionFound) {
            $regionFound = $address->region;
        }

        return $regionFound;
    }
    
    public function parseAddress($customer,$type) {
        $region = $this->getRegionByPostcode($customer->{$type});

        //Verifica se o país possui mais de 2 letras
        if(strlen($customer->{$type}->country) > 2){
            $country = Mage::getModel("directory/country")->loadByCode($customer->{$type}->country);

            $countryData = $country->getData();
            $countryIso2 = $countryData['iso2_code'];
            $directory = Mage::getModel("directory/region")->loadByCode($region, $countryIso2);
        }
        //Caso não, tenta carregar com o padrão iso-2
        else
            $directory = Mage::getModel("directory/region")->loadByCode($region, $customer->{$type}->country);
        
        $space = strpos($customer->{$type}->full_name,' ');
        if ($space !== false) {
            $firstname = substr($customer->{$type}->full_name, 0, $space);
            $lastname = substr($customer->{$type}->full_name,$space+1);
        } else {
            $firstname = $lastname = $customer->{$type}->full_name;
        } 
        
        $telephone = $fax = '';
        if (count($customer->customer->phones)) {
            $telephone = $customer->customer->phones[0];
            if (count($customer->customer->phones) > 1)
                $fax = $customer->customer->phones[1];
        }
        
        if (!trim($telephone)) {
        	$telephone = '(11) 1111-1111';
        }
        
        $address = array(
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     =>  'mkt@emailskyhub.com.br',
            'street' => array(
                $customer->{$type}->street,
                $customer->{$type}->number,
                $customer->{$type}->detail,
                $customer->{$type}->neighborhood
            ),
            'complemento' => $customer->{$type}->detail,
            'bairro' => $customer->{$type}->neighborhood,
            'number' => $customer->{$type}->number,
            'city' => $customer->{$type}->city,
            'region_id' => $directory->getRegionId(),
            'region' => $region,
            'postcode' => $customer->{$type}->postcode,
            'country_id' => $customer->{$type}->country,
            'telephone' =>  $telephone,
            'number'    => $fax,
            'celular'   => $fax,
            'fax' => $fax,
            'customer_password' => '',
            'vat_id' => $customer->customer->vat_number,
            'cpf' => $customer->customer->vat_number,
            'confirm_password' =>  '',
            'save_in_address_book' => '0',
            'use_for_shipping' => '1',
        );
        return $address;
    }
    
    public function paymentCode($channel) {
        return 'skyhub_' . strtolower(str_replace(' ','',$channel));
    }
    
    protected function _invoice($orderId, $status=NULL) {
    	if ($status == NULL) {
    		$status = 'ccadyennovo';
    	}
        try {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
	            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
	            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
	    
	            $invoice->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
	            $invoice->register();
	            $transactionSave = Mage::getModel('core/resource_transaction')
	            ->addObject($invoice)
	            ->addObject($invoice->getOrder());
	            $transactionSave->save();
	            
	            $order->addStatusToHistory(
	                $status, //update order status to processing after creating an invoice
	                'PAGAMENTO CONFIRMADO', true
                );
	            $order->save();
            } else {
            	Mage::throwException('NÃO FOI POSSÍVEL CARREGAR O PEDIDO: ' . $orderId);
            }
        } catch (Exception $e) {
            Mage::log('ERRO INVOICE: ' . $e->getMessage(),null,'onestic_skyhub.log');
        }
    }
    
    protected function _cancel($orderId) {
        $order = Mage::getModel('sales/order')->load($orderId);
        try {
        	if($order->canCancel()) {
        		$order->getPayment()->setMessage("Pagamento cancelado.");
        		$order->cancel();
        	} else { // ORDER ALREADY PAID, MUST CREATE CREDIT MEMO
        		$orderItem = $order->getItemsCollection();
        		$service = Mage::getModel('sales/service_order', $order);
        		$qtys = array();
        		foreach ($orderItem as $item) {
        			$qtys[$item->getId()] = $item->getQtyOrdered();
        		}
        		$data = array(
        				'qtys' => $qtys
        		);
        		$creditMemo = $service->prepareCreditmemo($data)->register()->save();
        		$comment = utf8_encode('Pagamento cancelado');
        		$order->addStatusHistoryComment($comment, 'canceled');
        	}
            $order->save();
        } catch (Exception $e) {
            Mage::log('ERRO CANCELAMENTO ' . $order->getIncrementId() . ': ' . $e->getMessage(),null,'onestic_skyhub.log');
        }
    }
    
    public function updateStatus($order, $newStatus) {
    	if ($newStatus == 'Cancelado') { // Cancela o pedido
    		$this->_cancel($order->getOrderId());
    	}
    	
    	if ($newStatus == 'aprovado') {
    		$createdAt = strtotime($order->getCreatedAt());
    		$limitDate = strtotime('2017-05-09 00:00:00');
    		$status = NULL;
    		if ($createdAt < $limitDate) {
    			$status = 'ccadyenimportado';
    		}
    		$this->_invoice($order->getOrderId(), $status);
    	}
    }
    
    public function getShipmentLabel($orderId) {
    	$api = Mage::getModel('onestic_skyhub/api_orders');
        $labels = $api->getShipmentLabels($orderId);
    	if ($labels['httpCode'] == 200) {
			$label = $labels['body'];
			if ($label->docsExternos[0]->plp) {
				$_control = Mage::getModel('onestic_skyhub/orders')->load($orderId);
				if ($_control->getId()) {
					$order = Mage::getModel('sales/order')->load($_control->getOrderId());
					if ($order->getId()) {
						$order->setSkyhubPlp($label->docsExternos[0]->plp->id);
						try {
							$order->save();
						} catch (Exception $e) {
							Mage::helper('onestic_skyhub')->log('ERRO SHIPMENT LABEL: ' . $e->getMessage());
						}
					}
				}
			}    		
    	}
    }
  
}
