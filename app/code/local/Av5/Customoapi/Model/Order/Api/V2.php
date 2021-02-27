<?php
class Av5_Customoapi_Model_Order_Api_V2 extends Mage_Sales_Model_Order_Api_V2
{
    /**
     * Retrieve full order information
     *
     * @param string $orderIncrementId
     *
     * @return array
     */
    public function info($orderIncrementId)
    {
        $result = parent::info($orderIncrementId);
        $order = parent::_initOrder($orderIncrementId);
        $result['marketplace_id'] = $order->getMarketplaceId();
        $result['marketplace'] = $order->getMarketplace();
        
        if ($demonstrativo = Mage::helper('av5_customoapi')->getDemonstrativoVidalink($order)) {
            $result['demonstrativo_vidalink'] = $demonstrativo;
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
            $transactionID = $payment->getAdditionalInformation ('1_TransactionIdentifier');
        } elseif ($method == 'paypal_standard') {
            $transactionID = $payment->getLastTransId();
            $data = 'Metodo: ' . $method;
        } elseif ($method == 'skyhub_magazineluiza') {
            $data = 'Metodo: ' . $method;
            $transactionID = "";
            $data .= PHP_EOL . 'Parcelas: ' . $payment->getAdditionalInformation ('parcels');
        } else {
            $data = 'Metodo: ' . $method;
            $transactionID = "";
        }
        $result['payment_infos'] = $data;
        $result['payment_transaction'] = $transactionID;

        if (!isset($result['mundipagg_interest'])) {
            $interest = ($order->getMundipaggInterest()) ? $order->getMundipaggInterest() : $order->getInterest();
            $result['mundipagg_interest'] = $interest;
        }

        $items = $order->getAllVisibleItems();
        foreach ($items as $key => $item):
            $productOptions = $item->getProductOptions();
            foreach ($productOptions['options'] as $option):
                if(strpos($option['label'], 'CRM') != false ) {
                    $result['items'][$key]['CRM'] = $option['value'];
                }
                if(strpos($option['label'], 'UF') != false ) {
                    $result['items'][$key]['UF'] = $option['value'];
                }
            endforeach;
        endforeach;
        
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

            if ($demonstrativo = Mage::helper('av5_customoapi')->getDemonstrativoVidalink($order)) {
                $orders[$index]['demonstrativo_vidalink'] = $demonstrativo;
            }
    
            $payment = $order->getPayment();
            $method = $payment->getMethod();
            if ($method == 'mundipagg_creditcard') {
                $data = 'Metodo: Cartao de Crdito';
                $data .= PHP_EOL . 'Chave do pedido: ' . $payment->getAdditionalInformation ('1_InstantBuyKey');
                $data .= PHP_EOL . 'OrderReference: ' . $payment->getAdditionalInformation ('OrderReference');
                $data .= PHP_EOL . 'Bandeira: ' . $payment->getAdditionalInformation ('1_CreditCardBrand');
                $data .= PHP_EOL . 'Numeracao: ' . $payment->getAdditionalInformation ('1_MaskedCreditCardNumber');
                $data .= PHP_EOL . 'Parcelas: ' . $payment->getAdditionalInformation ('mundipagg_creditcard_new_credito_parcelamento_1_1');
                $data .= PHP_EOL . 'Autorizacao: ' . $payment->getAdditionalInformation ('1_AuthorizationCode');
                $data .= PHP_EOL . 'ID da Transacao: ' . $payment->getAdditionalInformation ('1_TransactionIdentifier');
                $data .= PHP_EOL . 'Retorno adquirente: ' . $payment->getAdditionalInformation ('1_AcquirerMessage');
                $data .= PHP_EOL . 'Status da transacao: ' . $payment->getAdditionalInformation ('1_CreditCardTransactionStatus');
                $transactionID = $payment->getAdditionalInformation ('1_TransactionIdentifier');
            } elseif ($method == 'paypal_standard') {
                $transactionID = $payment->getLastTransId();
                $data = 'Metodo: ' . $method;
            } elseif ($method == 'skyhub_magazineluiza') {
                $data = 'Metodo: ' . $method;
                $transactionID = "";
                $data .= PHP_EOL . 'Parcelas: ' . $payment->getAdditionalInformation ('parcels');
            } else {
                $data = 'Metodo: ' . $method;
                $transactionID = "";
            }
            $orders[$index]['payment_infos'] = $data;
            $orders[$index]['payment_transaction'] = $transactionID;

            if (!isset($item['mundipagg_interest'])) {
                $interest = ($order->getMundipaggInterest()) ? $order->getMundipaggInterest() : $order->getInterest();
                $orders[$index]['mundipagg_interest'] = $interest;
            }

            $items = $order->getAllVisibleItems();
            foreach ($items as $item):
                $options = unserialize($item->getProductOptions());
                foreach ($options as $key => $label):
                    if(strpos($key, 'CRM') != false ) {
                        $orders[$index]['items'][$key]['CRM'] = $item->getValue();
                    }
                    if(strpos($key, 'UF') != false ) {
                        $orders[$index]['items'][$key]['UF'] = $item->getValue();
                    }
                endforeach;
            endforeach;
        }

        return $orders;
    }
}
