<?php 
class Av5_Customoapi_Model_Order_Api extends Mage_Sales_Model_Order_Api {
    
    public function info($orderIncrementId)
    {
        $result = parent::info($orderIncrementId);
        
        $order = parent::_initOrder($orderIncrementId);
        if (!isset($result['order']['marketplace_id'])) {
            $result['order']['marketplace_id'] = $order->getMarketplaceId();
            $result['order']['marketplace'] = $order->getMarketplace();
        }
        
        if (!isset($result['order']['vidalink_auth'])) {
            $result['order']['vidalink_auth'] = $order->getOvAutorizacao();
        }
        
        if (!isset($result['order']['customer_taxvat'])) {
        	$result['order']['customer_taxvat'] = $order->getCustomerTaxvat();
        }
        
        if (!isset($result['order']['skyhub_plp'])) {
        	$result['order']['skyhub_plp'] = $order->getSkyhubPlp();
        }
        
        if (!isset($result['order']['mundipagg_interest'])) {
            $interest = ($order->getMundipaggInterest()) ? $order->getMundipaggInterest() : $order->getInterest();
        	$result['order']['mundipagg_interest'] = $interest;
        }

        if ($demonstrativo = Mage::helper('av5_customoapi')->getDemonstrativoVidalink($order)) {
            $result['order']['demonstrativo_vidalink'] = $demonstrativo;
        }

        $payment = $order->getPayment();
        $method = $payment->getMethod();
        if ($method == 'mundipagg_creditcard') {
            $data = 'Método: Cartão de Crédito';
            $data .= PHP_EOL . 'Chave do pedido: ' . $payment->getAdditionalInformation ('1_InstantBuyKey');
            $data .= PHP_EOL . 'OrderReference: ' . $payment->getAdditionalInformation ('OrderReference');
            $data .= PHP_EOL . 'Bandeira: ' . $payment->getAdditionalInformation ('1_CreditCardBrand');
            $data .= PHP_EOL . 'Numeração: ' . $payment->getAdditionalInformation ('1_MaskedCreditCardNumber');
            $data .= PHP_EOL . 'Parcelas: ' . $payment->getAdditionalInformation ('mundipagg_creditcard_new_credito_parcelamento_1_1');
            $data .= PHP_EOL . 'Autorização: ' . $payment->getAdditionalInformation ('1_AuthorizationCode');
            $data .= PHP_EOL . 'ID da Transação: ' . $payment->getAdditionalInformation ('1_TransactionIdentifier');
            $data .= PHP_EOL . 'Retorno adquirente: ' . $payment->getAdditionalInformation ('1_AcquirerMessage');
            $data .= PHP_EOL . 'Status da transação: ' . $payment->getAdditionalInformation ('1_CreditCardTransactionStatus');
        } else {
            $data = 'Método: ' . $method;
        }
        $result['order']['payment_infos'] = $data;

        return $result;
    }
    
    public function items($filters = null) {
        $orders = parent::items($filters);
        foreach ($orders as $index => $item) {
            $order = parent::_initOrder($item['increment_id']);
            if (!isset($item['marketplace_id'])) {
                $orders[$index]['marketplace_id'] = $order->getMarketplaceId();
                $orders[$index]['marketplace'] = $order->getMarketplace();
            }
            
            if (!isset($item['vidalink_auth'])) {
                $orders[$index]['vidalink_auth'] = $order->getOvAutorizacao();
            }
            
            if (!isset($item['customer_taxvat'])) {
            	$orders[$index]['customer_taxvat'] = $order->getCustomerTaxvat();
            }

            if ($demonstrativo = Mage::helper('av5_customoapi')->getDemonstrativoVidalink($order)) {
                $orders[$index]['demonstrativo_vidalink'] = $demonstrativo;
            }

            if (!isset($item['mundipagg_interest'])) {
                $interest = ($order->getMundipaggInterest()) ? $order->getMundipaggInterest() : $order->getInterest();
                $orders[$index]['mundipagg_interest'] = $interest;
            }
            
            $payment = $order->getPayment();
            $method = $payment->getMethod();
            if ($method == 'mundipagg_creditcard') {
                $data = 'Método: Cartão de Crédito';
                $data .= PHP_EOL . 'Chave do pedido: ' . $payment->getAdditionalInformation ('1_InstantBuyKey');
                $data .= PHP_EOL . 'OrderReference: ' . $payment->getAdditionalInformation ('OrderReference');
                $data .= PHP_EOL . 'Bandeira: ' . $payment->getAdditionalInformation ('1_CreditCardBrand');
                $data .= PHP_EOL . 'Numeração: ' . $payment->getAdditionalInformation ('1_MaskedCreditCardNumber');
                $data .= PHP_EOL . 'Parcelas: ' . $payment->getAdditionalInformation ('mundipagg_creditcard_new_credito_parcelamento_1_1');
                $data .= PHP_EOL . 'Autorização: ' . $payment->getAdditionalInformation ('1_AuthorizationCode');
                $data .= PHP_EOL . 'ID da Transação: ' . $payment->getAdditionalInformation ('1_TransactionIdentifier');
                $data .= PHP_EOL . 'Retorno adquirente: ' . $payment->getAdditionalInformation ('1_AcquirerMessage');
                $data .= PHP_EOL . 'Status da transação: ' . $payment->getAdditionalInformation ('1_CreditCardTransactionStatus');
            } else {
                $data = 'Método: ' . $method;
            }
            $orders[$index]['payment_infos'] = $data;
        }
        
        return $orders;
    }
    
    
}