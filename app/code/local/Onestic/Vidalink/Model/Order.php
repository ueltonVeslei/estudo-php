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
 * @package    Onestic_Vidalink
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_Vidalink_Model_Order extends Varien_Object {

    const DEFAULT_GROUP_CODE = "VIDALINK";
    
    public function create($order) {
        try {
            if($order->IdPedido != "0"){
                $orderExists = Mage::getModel("sales/order")->loadByAttribute("ov_cotacao", $order->IdPedido);
                if ($orderExists->getId()) {
                    $result = array(
                        'IdRetornoPedido'       => $orderExists->getId(),
                        'StatusProcessamento'   => 1,
                        'Mensagem'              => ''
                    );
                    return $result;
                }
            }
            
            $groupName = substr($order->Cliente->Nome,0,32);
            $customerGroup = Mage::getModel('customer/group')->load($groupName, "customer_group_code");
            if (!$customerGroup->getId()) {
                $notLoggedInGroup = Mage::getModel('customer/group')->load(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                $customerGroup->setCode($order->Cliente->Nome);
                $customerGroup->setTaxClassId($notLoggedInGroup->getTaxClassId());
                $customerGroup->save();
            }
            
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $current_store_id = Mage::app()->getStore('default')->getId();
            $quote = Mage::getModel('sales/quote')->setStoreId($current_store_id);
            $quote->setIsSuperMode(true);
            //$quote->setReservedOrderId(Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($current_store_id));
            
            $this->prepareShippingMethod($order);
            $this->addItemsToQuote($quote, $order->Itens);
            $billingAddress = $this->parseAddress($order->Cliente,'EnderecoPrincipal');
            $shippingAddress = $this->parseAddress($order->ClienteRemessa,'EnderecoEntrega');
            
            $quote->getBillingAddress()
                    ->addData($billingAddress);

            $quote->getShippingAddress()
                    ->addData($shippingAddress)
                    ->setCollectShippingRates(true)
                    ->collectTotals()
                    ->setShippingMethod('vidalink_shipping')
                    ->setPaymentMethod('vidalink_payment');
            
            $customer = $order->ClienteRemessa;
            $space = strpos($customer->Nome,' ');
            $firstname = substr($customer->Nome, 0, $space);
            $lastname = substr($customer->Nome,$space);
            
            $quote->setCheckoutMethod('guest')
                ->setCustomerId(null)
                ->setCustomerFirstname($firstname)
                ->setCustomerLastname($lastname)
                ->setCustomerTaxvat($customer->CPF_CNPJ)
                ->setCustomerCpf($customer->CPF_CNPJ)
                ->setCpf($customer->CPF_CNPJ)
                ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId($customerGroup->getId());
            $quote->getPayment()->importData( array('method' => 'vidalink_payment'));
            $quote->save();
            
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            
            $mage_order = $service->getOrder();
            $mage_order->addStatusHistoryComment('OBSERVAÇÃO VIDALINK: ' . $order->Observacao . ' - AUTORIZAÇÃO VIDALINK: ' . $order->AutorizacaoVidalink);
            $mage_order->setOvAutorizacao($order->AutorizacaoVidalink);
            //$mage_order->setOvCotacao($order->IdCotacao);
            $mage_order->setOvCotacao($order->IdPedido);
            $mage_order->setOvOrigem($order->Origem);
            $mage_order->setOvReferencia($order->ReferenciaPedidoCliente);
            $mage_order->setMarketplace("Vidalink");
            $mage_order->setMarketplaceId($order->AutorizacaoVidalink);
            $mage_order->save();
            
            $result = array(
                'IdRetornoPedido'       => $mage_order->getId(),
                'StatusProcessamento'   => 1,
                'Mensagem'              => ''
            );
            
            $this->_invoice($mage_order->getId());
            
        } catch (Exception $e) {
            $result = array(
                'IdRetornoPedido'       => 0,
                'StatusProcessamento'   => 2,
                'Mensagem'              => $e->getMessage()
            );
        }
        return $result;
    }
    
    public function prepareShippingMethod($order){
        $shippingConfiguration = Mage::getSingleton("onestic_vidalink/shipping_configuration");
        $shippingConfiguration->setIsActive(true);
        $shippingConfiguration->setShippingMethodName('Entrega Vidalink');
        $shippingConfiguration->setShippingMethodCode("shipping");
        
        if (isset($order->ValorFreteAVista) && $order->ValorFreteAVista > 0) {
            $shippingPrice = $order->ValorFreteAVista;
        } elseif(isset($order->ValorFreteAReceber) && $order->ValorFreteAReceber > 0) {
            $shippingPrice = $order->ValorFreteAReceber;
        } else {
            $shippingPrice = 0;
        }
        
        $shippingConfiguration->setShippingPrice($shippingPrice);
    }
    
    public function addItemsToQuote($quote, $itemsData) {
        $itemsArray = (array)$itemsData;
        foreach($itemsArray as $itemEntry) {
            if (is_array($itemEntry)) {
                $item = $itemEntry[0];
            }else {
                $item = $itemEntry;
            }
            $product = Mage::getModel('catalog/product')->loadByAttribute('codigo_barras',$item->EAN);
            $status = '';
            if ($product) {
            	$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            	if ($stock->getQty() < $item->Quantidade || $stock->getIsInStock() != 1) {
            		$status = 'ProdutoForaDeEstoque';
            	}
            } else {
            	$status = 'ProdutoNaoEncontrado';
            }
            
            # ACONTECEU ALGUM PROBLEMA COM O PRODUTO
            if ($status) {
            	Mage::throwException($status.":".$item->EAN);
            	break;
            }
            
            $product = Mage::getModel('catalog/product')->load($product->getId());
            if (isset($item->PrecoUnitario)) {
            	$product->setPrice($item->PrecoUnitario);
            	$product->setSpecialPrice($item->PrecoUnitario);
            }
            $buyInfo = array(
            	'qty'           => $item->Quantidade,
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
            			$fullPath = Mage::getBaseDir() . DS . 'receita-padrao.gif';
            			$options[$id] = array(
    							'type' => 'application/octet-stream',
    							'title' => 'receita-padrao.gif',
    							'quote_path' => DS . 'receita-padrao.gif',
    							'order_path' => DS . 'receita-padrao.gif',
    							'fullpath' => $fullPath,
    							'size' => filesize($fullPath),
    							'width' => 1,
    							'height' => 1,
    							'secret_key' => substr(md5(file_get_contents($fullPath)), 0, 20),
    						);
            		}
            	}
            }
            if($options) {
            	$buyInfo['options'] = $options;
            }
            $quote->addProduct($product, new Varien_Object($buyInfo));
        }
    }
    
    public function parseAddress($customer,$type) {
        $directory = Mage::getModel("directory/region")->loadByCode($customer->{$type}->UF, 'BR');
        
        $space = strpos($customer->Nome,' ');
        $firstname = substr($customer->Nome, 0, $space);
        $lastname = substr($customer->Nome,$space); 
        
        $telephone = '(' . $customer->TelefoneResidencial->DDD . ')' . $customer->TelefoneResidencial->Numero;
        $fax = '(' . $customer->TelefoneCelular->DDD . ')' . $customer->TelefoneCelular->Numero;
        
        $address = array(
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     =>  $customer->Email,
            'street' => array(
                $customer->{$type}->Logradouro,
                $customer->{$type}->Numero,
                $customer->{$type}->Complemento,
                $customer->{$type}->Bairro
            ),
            'complemento' => $customer->{$type}->Complemento,
            'bairro' => $customer->{$type}->Bairro,
            'number' => $customer->{$type}->Numero,
            'city' => $customer->{$type}->Cidade,
            'region_id' => $directory->getRegionId(),
            'region' => $customer->{$type}->UF,
            'postcode' => substr('0'.$customer->{$type}->CEP,-8),
            'country_id' => 'BR',
            'telephone' =>  $telephone,
            'number'    => $fax,
            'celular'   => $fax,
            'fax' => $fax,
            'customer_password' => '',
            'vat_id' => $customer->CPF_CNPJ,
            'cpf' => $customer->CPF_CNPJ,
            'confirm_password' =>  '',
            'save_in_address_book' => '0',
            'use_for_shipping' => '1',
        );
        return $address;
    }
    
    protected function _invoice($orderId) {
        try {
            $order = Mage::getModel('sales/order')->load($orderId);
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
    
            $invoice->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
            
            $order->addStatusToHistory(
                'ccadyennovo', //update order status to processing after creating an invoice
                'PAGAMENTO CONFIRMADO', true
            );
            $order->save();
        } catch (Exception $e) {
            Mage::log('ERRO: ' . $e->getMessage(),null,'onestic_vidalink.log');
        }
    }
  
}