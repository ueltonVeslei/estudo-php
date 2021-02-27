<?php
class Controller_Payment extends Controller {

    protected $_messages = [
        ['Credit card type is not allowed for this payment method.','Tipo de cartÃ£o de crÃ©dito nÃ£o aceito.'],
        ['Please select your credit card type.','Por favor, selecione o tipo de cartÃ£o de crÃ©dito.'],
        ['Please enter your credit card holder name.','Por favor, informe o nome do titular do cartÃ£o de crÃ©dito.'],
        ['Invalid Credit Card Number','NÃºmero do cartÃ£o de crÃ©dito invÃ¡lido.'],
        ['Incorrect credit card expiration date.','Data de expiraÃ§Ã£o incorreta.'],
        ['Not all products are available in the requested quantity','Alguns produtos nÃ£o possuem estoque no momento.'],
        ['Lock wait timeout exceeded','NÃ£o conseguimos processar o seu pedido no momento, por favor tente novamente.'],
        ['Integrity constraint violation','NÃ£o conseguimos processar o seu pedido no momento, por favor tente novamente.'],
        ['The requested Payment Method is not available','Por favor, selecione uma forma de pagamento.'],
    ];

    // Retornar as formas de pagamento
    protected function _get()
    {

        if ($quoteID = $this->getData('ID')) {

            $mQuote = new Model_Quote($quoteID);

            $quote = $mQuote->getQuote();

            if (!$quote->getIsActive()) {
                $this->setResponse('status',Standard::STATUS500);
                $this->setResponse('data','Carrinho inválido');

                return false;
            }

            $payments = Mage::getSingleton('payment/config')->getActiveMethods();
            $helper = Mage::helper('payment');
            $helper->setLayout(Mage::app()->getLayout());

            foreach ($helper->getStoreMethods(Config::STORE) as $payment) {
                if ($quote->getIsRecurring() && $payment->getCode() == 'mundipagg_boleto') continue;
                $instance = $helper->getMethodInstance($payment->getCode());
                if ($instance->canUseCheckout()) {
                    $instance->setInfoInstance($quote->getPayment());
                    $formBlock = $helper->getMethodFormBlock($instance);

                    if ($formBlock) {
                        $dom = new DOMDocument();
                        $dom->loadHTML($formBlock->toHtml());
                        $objFormHtml = $this->element_to_obj($dom->documentElement);
                    }

                    $methods[] = array(
                        'label' => $payment->getTitle(),
                        'code'  => $payment->getCode(),
                        'html'  => $objFormHtml
                    );
                }
            }

            $this->setResponse('status',Standard::STATUS200);
            $this->setResponse('data',$methods);

        } else {

            $this->setResponse('status',Standard::STATUS500);
            $this->setResponse('data','Dados nÃ£o informados');

        }
    }

    // Indica o mÃ©todo de pagamento selecionado
    protected function _post()
    {
        if ($body = $this->getData('body')) {
            $mQuote = new Model_Quote($body->quote_id);
            $quote = $mQuote->getQuote();

            if (isset($_SERVER['HTTP_REFERER'])) {
                $referer = parse_url($_SERVER['HTTP_REFERER']);

                if (isset($referer['query'])) {
                    parse_str($referer['query'], $qstr);

                    if (isset($qstr['t'])) {
                        if ($qstr['t'] != $quote->getDscToken()) {
                            $this->setResponse('status', Standard::STATUS500);
                            $this->setResponse('data', 'Carrinho invÃ¡lido');

                            return false;
                        }
                    }
                }
            }

            if (!$quote->getIsActive()) {
                $this->setResponse('status',Standard::STATUS500);
                $this->setResponse('data','Pedido jÃ¡ finalizado');

                return false;
            }

            if (!$quote->getDscToken()) {
                $this->setResponse('status',Standard::STATUS500);
                $this->setResponse('data','Carrinho invÃ¡lido');

                return false;
            }

            if (isset($body->customer_id)) {
                $customer = Mage::getModel('customer/customer')->load($body->customer_id);

                if ($quote->getCustomer()->getId()) {
                    if ($quote->getCustomer()->getId() != $customer->getId()) {
                        $this->setResponse('status',Standard::STATUS500);
                        $this->setResponse('data','Dados invÃ¡lidos');

                        return false;
                    }
                }

                $quote->setCustomer($customer);
                $quote->save();
            }

            if (isset($body->payment_data)) {
                try {
                    //Mage::log('PAYMENT_DATA' . json_encode($body),null,'dsc_payment_data.log');
                    $method = $body->payment_data->method;

                    if (!$quote->isVirtual()) {
                        $quote->getShippingAddress()->setPaymentMethod($method);
                    } else {
                        $quote->getBillingAddress()->setPaymentMethod($method);
                    }

                    $paymentData = (array)$body->payment_data;
                    $_POST['payment'] = $paymentData;

                    $quote->getPayment()->importData($paymentData + [
                            'payment_method' => $method,
                        ]);
                    $quote->assignCustomer($customer);

                    $service = Mage::getModel('sales/service_quote', $quote);
                    $service->submitAll();

                    $checkoutSession = Mage::getSingleton('checkout/session');
                    $checkoutSession->setLastQuoteId($quote->getId())
                        ->setLastSuccessQuoteId($quote->getId())
                        ->clearHelperData();

                    $order = $service->getOrder();
                    $order->setCustomer($customer);
                    $order->setCustomerId($customer->getId());
                    $order->setCustomerFirstname($customer->getFirstname());
                    $order->setCustomerLastname($customer->getLastname());
                    $order->setCustomerEmail($customer->getEmail());
                    $order->save();
                    if ($order) {
                        Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                            array('order'=>$order, 'quote'=>$quote));

                        /**
                         * a flag to set that there will be redirect to third party after confirmation
                         * eg: paypal standard ipn
                         */
                        $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();

                        /**
                         * we only want to send to customer about new order when there is no redirect to third party
                         */
                        // OrderEmail::send($order);
                        // if ($order->getCanSendNewEmailFlag()) {
                        //     try {
                        //         $order->queueNewOrderEmail();
                        //     } catch (Exception $e) {
                        //         Mage::logException($e);
                        //     }
                        // }

                        // add order information to the session
                        $checkoutSession->setLastOrderId($order->getId())
                            ->setRedirectUrl($redirectUrl)
                            ->setLastRealOrderId($order->getIncrementId());

                        // as well a billing agreement can be created
                        $agreement = $order->getPayment()->getBillingAgreement();
                        if ($agreement) {
                            $checkoutSession->setLastBillingAgreementId($agreement->getId());
                        }
                    }

                    $profiles = $service->getRecurringPaymentProfiles();
                    if ($profiles) {
                        $ids = array();
                        foreach ($profiles as $profile) {
                            $ids[] = $profile->getId();
                        }
                        $checkoutSession->setLastRecurringProfileIds($ids);
                        // TODO: send recurring profile emails
                    }

                    Mage::dispatchEvent(
                        'checkout_submit_all_after',
                        array('order' => $order, 'quote' => $quote, 'recurring_profiles' => $profiles)
                    );

                    Mage::dispatchEvent(
                        'checkout_onepage_controller_success_action',
                        ['order_ids' => [$order->getId()]]
                    );

                    $quote->setIsActive(0)->save();

                    $this->setResponse('status',Standard::STATUS200);
                    $this->setResponse('data',$order->getData());

                } catch(Exception $e) {

                    $quote->setIsActive(1)->save();

                    $errors = $this->_translateMessages($e->getMessage());
                    Mage::log('PAYMENT ERRORS: ' . json_encode($errors),null,'dsc_payment_data.log');

                    $this->setResponse('status',Standard::STATUS400);
                    $this->setResponse('data',['errors' => $errors]);
                }

            } else {

                $this->setResponse('status',Standard::STATUS400);
                $this->setResponse('data','Dados de pagamento nÃ£o informados');

            }

        } else {

            $this->setResponse('status',Standard::STATUS400);
            $this->setResponse('data','Dados nÃ£o informados');

        }

    }

    protected function _delete()
    {
        if ($body = $this->getData('body')) {
            $mQuote = new Model_Quote($body->quote_id);
            $quote = $mQuote->getQuote();
            $customer = Mage::getModel('customer/customer')->load($body->customer_id);
            $quote->setCustomer($customer);
            $quote->save();
            if (isset($body->payment_data)) {
                $method = $body->payment_data->method;
                if (!$quote->isVirtual()) {
                    $quote->getShippingAddress()->setPaymentMethod($method);
                } else {
                    $quote->getBillingAddress()->setPaymentMethod($method);
                }
                $paymentData = (array)$body->payment_data;
                $_POST['payment'] = $paymentData;
                $quote->getPayment()->importData($paymentData + [
                        'payment_method' => $method,
                    ]);
                $quote->assignCustomer($customer);
                $quote->setTotalsCollectedFlag(false)->collectTotals();
                $quote->save();
                try {
                    $mQuote2 = new Model_Quote($body->quote_id);
                    $this->setResponse('status',Standard::STATUS200);
                    $this->setResponse('data',QuoteTransform::transform($mQuote2));
                } catch(Exception $e) {
                    $this->setResponse('status',Standard::STATUS500);
                    $this->setResponse('data','ERRO: ' . $e->getMessage());
                }
            } else {
                $this->setResponse('status',Standard::STATUS500);
                $this->setResponse('data','Dados de pagamento nÃ£o informados');
            }
        } else {
            $this->setResponse('status',Standard::STATUS500);
            $this->setResponse('data','Dados nÃ£o informados');
        }
    }

    //Criacao das parcelas no cartao de credito
    protected function _put()
    {

        $body = $this->getData('body');
        $mQuote = new Model_Quote($body->quote_id);
        $quote = $mQuote->getQuote();
        if ($quote->getIsRecurring()) {
            $formattedValue = Mage::helper('core')->currency(floatval($body->value), true, false);
            $installments['1'] = '1 x ' . $formattedValue . ' s/ juros (Total: ' . $formattedValue . ')';
        } else {
            $brandName = ($body->ctype) ? $body->ctype : null;
            $installmentConfig = Mage::helper('mundipagg/installments');

            $grandTotal = floatval($body->value);

            if($body->value === null) {

                $grandTotal = Mage::getModel('checkout/session')
                    ->getQuote()->getGrandTotal();

            }

            $installments = $installmentConfig->getInstallmentForCreditCardType(
                $brandName,
                $grandTotal
            );

            foreach ($installments as $key => $installment) {
                $installments[$key] = str_replace('without interest', 's/ juros', $installments[$key]);
                $installments[$key] = str_replace('with interest', 'c/ juros', $installments[$key]);
                $installments[$key] = str_replace('Total: ', 'Total: R$', $installments[$key]);
            }
        }
        //1 x R$  1850,00 without interest (Total: 1850,00)

        $this->setResponse('status',Standard::STATUS200);
        $this->setResponse('data',$installments);
    }

    //FUNCAO PARA TRATAMENTO DO FORM
    protected function element_to_obj($element)
    {

        $obj = array( "tag" => $element->tagName );

        foreach ($element->attributes as $attribute) {

            $obj[$attribute->name] = $attribute->value;

        }

        foreach ($element->childNodes as $subElement) {

            if ($subElement->nodeType == XML_TEXT_NODE) {

                $obj["html"] = $subElement->wholeText;

            } else {

                $obj["children"][] = $this->element_to_obj($subElement);

            }

        }

        return $obj;

    }

    protected function _translateMessages($message) {
        $errorMessages = [];
        foreach ($this->_messages as $msg) {
            if (strpos($message,$msg[0]) !== false) {
                $errorMessages = $msg[1];
            }
        }

        return $errorMessages;
    }

}