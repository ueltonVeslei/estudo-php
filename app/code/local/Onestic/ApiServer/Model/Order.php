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
 * @package    Onestic_ApiServer
 * @copyright  Copyright (c) 2016 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_ApiServer_Model_Order extends Varien_Object {

    const DEFAULT_GROUP_CODE = "ONESTIC_ROCHE";
    const DEFAULT_GROUP_PREFIX = "ONESTIC_";

    protected function getCustomerGroupCode($order)
    {
        if ($order->Empresa->NomeFantasia) {
            return self::DEFAULT_GROUP_PREFIX . $order->Empresa->NomeFantasia;
        }
        return self::DEFAULT_GROUP_CODE;
    }

    public function create($order) {
        Mage::log(json_encode($order),null,'apiserver.log',true);
        try {
            $customerGroupCode = $this->getCustomerGroupCode($order);
            $customerGroup = Mage::getModel('customer/group')->load($customerGroupCode, "customer_group_code");
            if (!$customerGroup->getId()) {
                $notLoggedInGroup = Mage::getModel('customer/group')->load(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                $customerGroup->setCode($customerGroupCode);
                $customerGroup->setTaxClassId($notLoggedInGroup->getTaxClassId());
                $customerGroup->save();
            }
            
			Mage::helper('onestic_apiserver')->log('PEDIDO: ' . var_export($order,true));
			
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $current_store_id = Mage::app()->getStore('default')->getId();
            $quote = Mage::getModel('sales/quote')->setStoreId($current_store_id);
            $quote->setIsSuperMode(true);
            //$quote->setReservedOrderId(Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($current_store_id));
            
            $this->prepareShippingMethod($order);
            $this->addItemsToQuote($quote, $order->Itens);
            $billingAddress = $this->parseAddress($order->Cliente,'EnderecoPrincipal', $order);
            $shippingAddress = $this->parseAddress($order->Cliente,'EnderecoEntrega', $order);
            
            $quote->getBillingAddress()
                    ->addData($billingAddress);

            $quote->getShippingAddress()
                    ->addData($shippingAddress)
                    ->setCollectShippingRates(true)
                    ->collectTotals()
                    ->setShippingMethod('apiserver_shipping')
                    ->setPaymentMethod('apiserver_payment');
            
            $customer = $order->Cliente;
            $space = strpos(trim($customer->Nome),' ');
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
                
                if ($order->Empresa->NomeFantasia == 'NEBZMART') {
                    $quote->getBillingAddress()->setTelephone($customer->TelefoneResidencial);
                }


            $quote->getPayment()->importData( array('method' => 'apiserver_payment'));
            $quote->save();
            
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            
            $mage_order = $service->getOrder();

            if (isset($order->Observacao)){
                if ($order->Empresa->NomeFantasia == 'NEBZMART') {
                    $dadosPagto = json_decode($order->DadosPagamento);
                    if ($order->FormaPagamento == 'mundipagg_boleto') {
                        $obs_comment = 'Chave do Pedido: ' . $dadosPagto->OrderKey;
                        $obs_comment .= ' - Referência do Pedido: ' . $dadosPagto->OrderReference;
                        $obs_comment .= ' - Nosso Número: ' . $dadosPagto->{'1_NossoNumero'};
                        $obs_comment .= ' - Código de Barras: ' . $dadosPagto->{'1_Barcode'};
                    }

                    if ($order->FormaPagamento == 'mundipagg_creditcard') {
                        $obs_comment = "Chave do Pedido: " . $dadosPagto->OrderKey[0];
                        $obs_comment .= ' - Referência do Pedido: ' . $dadosPagto->OrderReference;
                        $obs_comment .= " - Bandeira: " . $dadosPagto->mundipagg_creditcard_1_1_cc_type;
                        $obs_comment .= " - Número mascarado: " . $dadosPagto->{'1_MaskedCreditCardNumber'};
                        $obs_comment .= " - Valor Pago: " . $dadosPagto->baseGrandTotal;
                        $obs_comment .= " - Parcelas: " . $dadosPagto->mundipagg_creditcard_new_credito_parcelamento_1_1;
                        $obs_comment .= " - Código de Autorização: " . $dadosPagto->{'1_TransactionKey'};
                    }
                    $mage_order->addStatusHistoryComment('OBSERVAÇÃO DE PAGAMENTO -> ' . $obs_comment);

                    $mage_order->setShippingDescription($order->FormaEntrega);

                    $mage_order->addStatusHistoryComment('PEDIDO NEBZMART: ' . $order->ReferenciaPedidoCliente);
                    $mage_order->setOvOrigem("NEBZMART");
                    $mage_order->setMarketplace("onestic_nebzmart");
                } else {
                    $dados_pagto = unserialize($order->Observacao);
                    if($dados_pagto){
                        $obs_comment =  "Status: " . $dados_pagto['raw_details_info']['status'];
                        $obs_comment .= " - Código de Autorização: " . $dados_pagto['raw_details_info']['authorization_code'];
                        $obs_comment .= " - Total Pago: " . $dados_pagto['raw_details_info']['paid_amount'];
                        $obs_comment .= " - Parcelas: " . $dados_pagto['raw_details_info']['installments'];
                        $obs_comment .= " - Nome no Cartão: " . $dados_pagto['raw_details_info']['card_holder_name'];
                        $obs_comment .= " - Cartão: " . $dados_pagto['raw_details_info']['card_first_digits'] . "......" . $dados_pagto['raw_details_info']['card_last_digits'];
                        $obs_comment .= " - Bandeira: " . $dados_pagto['raw_details_info']['card_brand'];
                        $obs_comment .= " - Método de Pagamento: " . $dados_pagto['raw_details_info']['payment_method'];
                        $mage_order->addStatusHistoryComment('OBSERVAÇÃO DE PAGAMENTO -> ' . $obs_comment);
                    }
                    $mage_order->addStatusHistoryComment('PEDIDO ROCHE: ' . $order->ReferenciaPedidoCliente);
                    $mage_order->setOvOrigem("ROCHE");
                    $mage_order->setMarketplace("ROCHE");
                }
            }


            if($order->Origem){
                $mage_order->addStatusHistoryComment('Código EAN: ' . $order->Origem);
            }
            $mage_order->setOvAutorizacao($order->AutorizacaoApiServer);
            $mage_order->setOvCotacao($order->IdCotacao);
            $mage_order->setOvReferencia($order->ReferenciaPedidoCliente);

            $mage_order->setMarketplaceId($order->ReferenciaPedidoCliente);
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
                'StatusProcessamento'   => 1, // Sempre retorna 1 para incluir o pedido na fila
                'Mensagem'              => $e->getMessage()
            );
        }
        Mage::helper('onestic_apiserver')->log('PEDIDO result: ' . var_export($result,true));
        return $result;
    }
	
    public function EnviarPedido($order) {
        try {
            /*$orderExists = Mage::getModel("sales/order")->loadByAttribute("ov_referencia", $order->ReferenciaPedidoCliente);
            if ($orderExists->getId()) {
                Mage::throwException("Pedido já registrado!");
            }*/
            
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
            //$quote->setReservedOrderId(Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($current_store_id));
            
            $this->prepareShippingMethod($order);
            $this->addItemsToQuote($quote, $order->Itens);
            $billingAddress = $this->parseAddress($order->Cliente,'EnderecoPrincipal', $order);
            $shippingAddress = $this->parseAddress($order->ClienteRemessa,'EnderecoEntrega', $order);
            
            $quote->getBillingAddress()
                    ->addData($billingAddress);

            $quote->getShippingAddress()
                    ->addData($shippingAddress)
                    ->setCollectShippingRates(true)
                    ->collectTotals()
                    ->setShippingMethod('apiserver_shipping')
                    ->setPaymentMethod('apiserver_payment');
            
            $customer = $order->ClienteRemessa;
            $space = strpos(trim($customer->Nome),' ');
            $firstname = trim($customer->Nome);
            $lastname = trim($customer->Nome);
            
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
            $quote->getPayment()->importData( array('method' => 'apiserver_payment'));
            $quote->save();
            
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            
            $mage_order = $service->getOrder();
            $mage_order->addStatusHistoryComment('OBSERVAÇÃO ROCHE: ' . $order->Observacao);
            $mage_order->setOvAutorizacao($order->AutorizacaoApiServer);
            $mage_order->setOvCotacao($order->IdCotacao);
            $mage_order->setOvOrigem($order->Origem);
            $mage_order->setOvReferencia($order->ReferenciaPedidoCliente);
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
        $shippingConfiguration = Mage::getSingleton("onestic_apiserver/shipping_configuration");
        $shippingConfiguration->setIsActive(true);
        $shippingConfiguration->setShippingMethodName('Entrega ApiServer');
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
			if($item->Tipo == 'simple'){
				$product = Mage::getModel('catalog/product')->loadByAttribute('codigo_barras',$item->EAN);
				$status = '';
				Mage::log($item->EAN . " - " . $product->getTypeId(), null, 'onestic_apiserver.log');
				//Mage::log($product, null, 'onestic_apiserver.log');
				//die;
				if ($product) {
                    if($product->getTypeId() == "grouped"){
                        $itens = array();
                        $products = $product->getTypeInstance(true)->getAssociatedProducts($product);
                            foreach($products as $p){
                                 Mage::log("Dentro do Grouped-> " . $p->getCodigoBarras(), null, 'onestic_apiserver.log');
                                $itens[] = (object)array(
                                    'EAN'                           => $p->getCodigoBarras(),
                                    'Quantidade'                    => $p->getQty(),
                                    'Tipo'                          => 'simple'
                                );
                            }
                        $this->addItemsToQuote($quote, $itens);
                        continue;
                    }

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
    }
    
    public function parseAddress($customer,$type,$order) {
		$uf_final = $this->_uf($customer->{$type}->UF);
        $directory = Mage::getModel("directory/region")->loadByCode($uf_final, 'BR');
        
        $space = strpos(trim($customer->Nome),' ');
        $firstname = substr($customer->Nome, 0, $space);
        $lastname = substr($customer->Nome,$space); 

        if ($order->Empresa->NomeFantasia == 'NEBZMART') {
            $telephone = $customer->TelefoneResidencial;
            $fax = $customer->TelefoneCelular;
        } else {
            $telephone = '(' . $customer->TelefoneResidencial->DDD . ')' . $customer->TelefoneResidencial->Numero;
            $fax = '(' . $customer->TelefoneCelular->DDD . ')' . $customer->TelefoneCelular->Numero;
        }
        
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
            'region' => $uf_final,
            'postcode' => $customer->{$type}->CEP,
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
            Mage::log('ERRO: ' . $e->getMessage(),null,'onestic_apiserver.log');
        }
    }
	
    protected function _uf($uf_extenso) {
		$estadosBrasileiros = array(
		'AC'=>'Acre',
		'AL'=>'Alagoas',
		'AP'=>'Amapá',
		'AM'=>'Amazonas',
		'BA'=>'Bahia',
		'CE'=>'Ceará',
		'DF'=>'Distrito Federal',
		'ES'=>'Espírito Santo',
		'GO'=>'Goiás',
		'MA'=>'Maranhão',
		'MT'=>'Mato Grosso',
		'MS'=>'Mato Grosso do Sul',
		'MG'=>'Minas Gerais',
		'PA'=>'Pará',
		'PB'=>'Paraíba',
		'PR'=>'Paraná',
		'PE'=>'Pernambuco',
		'PI'=>'Piauí',
		'RJ'=>'Rio de Janeiro',
		'RN'=>'Rio Grande do Norte',
		'RS'=>'Rio Grande do Sul',
		'RO'=>'Rondônia',
		'RR'=>'Roraima',
		'SC'=>'Santa Catarina',
		'SP'=>'São Paulo',
		'SE'=>'Sergipe',
		'TO'=>'Tocantins'
		);
		
		if(strlen($uf_extenso) > 2){
			$uf_final = array_search($uf_extenso, $estadosBrasileiros);
		}else{
			$uf_final = $uf_extenso;
		}
		
		return $uf_final;
    }	
  
}