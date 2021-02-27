<?php

class Uecommerce_Mundipagg_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Availability options
     */
    protected $_code = 'mundipagg_standard';
    protected $_formBlockType = 'mundipagg/standard_form';
    protected $_infoBlockType = 'mundipagg/info';
    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_canFetchTransactionInfo = false;
    protected $_canManageRecurringProfiles = false;
    protected $_allowCurrencyCode = ['BRL', 'USD', 'EUR'];
    protected $_isInitializeNeeded = true;
    private $brands = [
        'VI' => 'Visa',
        'MC' => 'Mastercard',
        'AE' => 'Amex',
        'DI' => 'Diners',
        'HI' => 'Hipercard',
        'EL' => 'Elo'
    ];
    CONST WITH_ERROR_MSG =
        'Transaction status: WithError. Waiting for notification post';

    CONST OFFLINE_RETRY_MSG =
        'Payment not authorized, but offline retry is enabled.
         Waiting for notification post';

    /**
     * Transaction ID
     * */
    protected $_transactionId = null;

    /**
     * CreditCardOperationEnum na gateway
     * @var $CreditCardOperationEnum varchar
     */
    private $_creditCardOperationEnum;

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setmerchantKey($merchantKey)
    {
        $this->merchantKey = $merchantKey;
    }

    public function getmerchantKey()
    {
        return $this->merchantKey;
    }

    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function setPaymentMethodCode($paymentMethodCode)
    {
        $this->paymentMethodCode = $paymentMethodCode;
    }

    public function getPaymentMethodCode()
    {
        return $this->paymentMethodCode;
    }

    public function setAntiFraud($antiFraud)
    {
        $this->antiFraud = $antiFraud;
    }

    public function getAntiFraud()
    {
        return $this->antiFraud;
    }

    public function setBankNumber($bankNumber)
    {
        $this->bankNumber = $bankNumber;
    }

    public function getBankNumber()
    {
        return $this->bankNumber;
    }

    public function setDebug($debug)
    {
        $this->_debug = $debug;
    }

    public function getDebug()
    {
        return $this->_debug;
    }

    public function setDiasValidadeBoleto($diasValidadeBoleto)
    {
        $this->_diasValidadeBoleto = $diasValidadeBoleto;
    }

    public function getDiasValidadeBoleto()
    {
        return $this->_diasValidadeBoleto;
    }

    public function setInstrucoesCaixa($instrucoesCaixa)
    {
        $this->_instrucoesCaixa = $instrucoesCaixa;
    }

    public function getInstrucoesCaixa()
    {
        return $this->_instrucoesCaixa;
    }

    public function setCreditCardOperationEnum($creditCardOperationEnum)
    {
        $this->_creditCardOperationEnum = $creditCardOperationEnum;
    }

    public function getCreditCardOperationEnum()
    {
        return $this->_creditCardOperationEnum;
    }

    public function setParcelamento($parcelamento)
    {
        $this->parcelamento = $parcelamento;
    }

    public function getParcelamento()
    {
        return $this->parcelamento;
    }

    public function setParcelamentoMax($parcelamentoMax)
    {
        $this->parcelamentoMax = $parcelamentoMax;
    }

    public function getParcelamentoMax()
    {
        return $this->parcelamentoMax;
    }

    public function setPaymentAction($paymentAction)
    {
        $this->paymentAction = $paymentAction;
    }

    public function getPaymentAction()
    {
        return $this->paymentAction;
    }

    public function setCieloSku($cieloSku)
    {
        $this->cieloSku = $cieloSku;
    }

    public function getCieloSku()
    {
        return $this->cieloSku;
    }

    public function __construct($Store = null)
    {
        if (!($Store instanceof Mage_Core_Model_Store)) {
            $Store = null;
        }

        $this->setEnvironment($this->getConfigData('environment', $Store));

        switch ($this->getEnvironment()) {
            case 'localhost':
            case 'development':
            case 'staging':
            default:
                $environment = 'Staging';
                $this->setPaymentMethodCode(1);
                $this->setBankNumber(341);
                break;
            case 'production':
                $environment = 'Production';
                break;
        }
        $this->setmerchantKey(trim($this->getConfigData('merchantKey' . $environment, $Store)));
        $this->setUrl(trim($this->getConfigData('apiUrl' . $environment, $Store)));
        $this->setPaymentAction($this->getConfigData('payment_action', $Store));
        $this->setAntiFraud($this->getConfigData('antifraud', $Store));
        $this->setParcelamento($this->getConfigData('parcelamento', $Store));
        $this->setParcelamentoMax($this->getConfigData('parcelamento_max', $Store));
        $this->setDebug($this->getConfigData('debug', $Store));
        $this->setEnvironment($this->getConfigData('environment', $Store));
        $this->setCieloSku($this->getConfigData('cielo_sku', $Store));
    }

    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $code = $this->getCode();
        $path = 'payment/' . $code . '/' . $field;
        $data = Mage::getStoreConfig($path, $storeId);
        if (!$data && $code != 'mundipagg_standard') {
            $path = 'payment/mundipagg_standard/' . $field;
            $data = Mage::getStoreConfig($path, $storeId);
        }
        return $data;
    }

    /**
     * Armazena as informações passadas via formulário no frontend
     * @access public
     * @param array $data
     * @return Uecommerce_Mundipagg_Model_Standard
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $mundipagg = [];
        $helper = Mage::helper('mundipagg');

        foreach ($data->getData() as $id => $value) {
            $mundipagg[$id] = $value;
            $this->checkCpf($id, $value);
        }

        $info->setAdditionalInformation('CountCcs', $mundipagg['countccs']);

        $helperInstallments = Mage::helper('mundipagg/Installments');

        $methodsWithInstallments = [
            'mundipagg_creditcard',
            'mundipagg_twocreditcards'
        ];

        if (
            $helperInstallments->isInstallmentsEnabled() !== '0' &&
            in_array($mundipagg['method'], $methodsWithInstallments)
        ) {
            $dataKeys = array_keys($mundipagg);
            $ccIndexes = array_filter($dataKeys, function($key) {
                return strpos($key,'_token_') !== false;
            });

            $validInstallments = $helperInstallments->getInstallmentForCreditCardType();

            foreach ($ccIndexes as $tokenIndex) {
                $cardId = substr($tokenIndex,strlen($tokenIndex)-3);

                $new = $mundipagg[$tokenIndex] === 'new' ? "_new" : "";

                $installmentValue = $mundipagg[
                $mundipagg["method"] . $new .
                "_credito_parcelamento_" . $cardId
                ];
                if (
                    !isset($validInstallments[$installmentValue]) &&
                    $mundipagg["method"] !== "mundipagg_creditcardoneinstallment"
                ) {
                    Mage::throwException("MP - Invalid installment number: ($installmentValue)");
                }
            }
        }

        if (!empty($mundipagg)) {
            $helperInstallments = Mage::helper('mundipagg/Installments');

            //Set Mundipagg Data in Session
            $session = Mage::getSingleton('checkout/session');
            $session->setMundipaggData($mundipagg);

            $info = $this->getInfoInstance();

            if (isset($mundipagg['mundipagg_type'])) {
                $info->setAdditionalInformation('PaymentMethod', $mundipagg['method']);

                switch ($mundipagg['method']) {
                    case 'mundipagg_creditcardoneinstallment':
                    case 'mundipagg_creditcard':
                        try {

                            if ($mundipagg['method'] == "mundipagg_creditcard") {
                                $mundipagg['mundipagg_creditcard_1_1_cc_type_max_installments'] =
                                    $helperInstallments->getMaxInstallments(
                                        $mundipagg['mundipagg_creditcard_1_1_cc_type']
                                    );
                            }

                            $this->saveCreditCardAdditionalInformation($mundipagg, $info);
                        } catch (Exception $e) {
                            $helperLog = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
                            $helperLog->error($e->getMessage(), true);

                            return false;
                        }
                        break;

                    default:
                        $info->setCcType(null)
                            ->setCcOwner(null)
                            ->setCcLast4(null)
                            ->setCcNumber(null)
                            ->setCcCid(null)
                            ->setCcExpMonth(null)
                            ->setCcExpYear(null);

                        break;
                }

                $this->saveAllAdditionalInformation($mundipagg, $info, $helper);
                $this->validateInstallmentsAmount($mundipagg, $info, $helper, $helperInstallments);
            } else {
                if (isset($mundipagg['method'])) {
                    $info->setAdditionalInformation(
                        'PaymentMethod', $mundipagg['method']
                    );
                }
            }
        }

        // Get customer_id from Quote (payment made on site) or from POST (payment made from API)
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            if ($this->getQuote()->getCustomer()->getEntityId()) {
                $customerId = $this->getQuote()->getCustomer()->getEntityId();
            }
        } elseif (isset($mundipagg['entity_id'])) {
            $customerId = $mundipagg['entity_id'];
        }

        // We verifiy if token is from customer
        if (isset($customerId) && isset($mundipagg['method'])) {
            $num = $helper->getCreditCardsNumber($mundipagg['method']);

            if ($num == 0) {
                $num = 1;
            }

            foreach ($mundipagg as $key => $value) {
                $pos = strpos($key, 'token_' . $num);

                if ($pos !== false && $value != '' && $value != 'new') {
                    $token = Mage::getModel('mundipagg/cardonfile')->load($value);

                    // Ok
                    if ($token->getId() && $token->getEntityId() == $customerId) {
                        $info->setAdditionalInformation('CreditCardBrandEnum_' . $key, $token->getCcType());
                    } else {
                        $error = $helper->__('Token not found');

                        Mage::log($error, null, 'Uecommerce_Mundipagg.log');
                        Mage::throwException($error);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return Uecommerce_Mundipagg_Model_Standard
     * @throws Varien_Exception
     */
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        if ($this->_canSaveCc) {
            $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        }

        $info->setCcNumber(null);

        return $this;
    }

    /**
     * Get payment quote
     */
    public function getPayment()
    {
        return $this->getQuote()->getPayment();
    }

    /**
     * Get Modulo session namespace
     *
     * @return Uecommerce_Mundipagg_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('mundipagg/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Check order availability
     *
     * @return bool
     */
    public function canOrder()
    {
        return $this->_canOrder;
    }

    /**
     * Check authorize availability
     *
     * @return bool
     */
    public function canAuthorize()
    {
        return $this->_canAuthorize;
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        return $this->_canCapture;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param $stateObject
     * @return Uecommerce_Mundipagg_Model_Standard
     * @throws Varien_Exception
     */
    public function initialize($paymentAction, $stateObject)
    {
        // TODO move initialize method to appropriate model (Boleto, Creditcard ...)
        $paymentAction = $this->getPaymentAction();

        switch ($paymentAction) {
            case 'order':
                $this->setCreditCardOperationEnum('AuthAndCapture');
                break;

            case 'authorize':
                $this->setCreditCardOperationEnum('AuthOnly');
                break;

            case 'authorize_capture':
                $this->setCreditCardOperationEnum('AuthAndCaptureWithDelay');
                break;
        }

        $orderAction = Mage_Payment_Model_Method_Abstract::ACTION_ORDER;
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();

        // If payment method is Boleto Bancário we call "order" method
        if ($payment->getAdditionalInformation('PaymentMethod') == 'mundipagg_boleto') {
            $this->order($payment, $order->getBaseTotalDue());

            return $this;
        }

        // If it's a multi-payment types we force to ACTION_AUTHORIZE
        $num = Mage::helper('mundipagg')->getCreditCardsNumber(
            $payment->getAdditionalInformation('PaymentMethod')
        );

        if ($num > 1) {
            $paymentAction = Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
        }

        switch ($paymentAction) {
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
                $payment->authorize($payment, $order->getBaseTotalDue());
                break;

            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
                $payment->authorize($payment, $order->getBaseTotalDue());
                break;

            case $orderAction:
                $this->order($payment, $order->getBaseTotalDue());
                break;

            default:
                $this->order($payment, $order->getBaseTotalDue());
                break;
        }
    }

    /**
     * Authorize payment abstract method
     *
     * @param Varien_Object $payment
     * @param float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        try {
            if (!$this->canAuthorize()) {
                Mage::throwException(Mage::helper('payment')->__('Authorize action is not available.'));
            }

            // Load order
            $order = $payment->getOrder();

            $helperLog = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
            $helperLog->setLogLabel('|' . $order->getIncrementId() . '|');

            // Proceed to authorization on Gateway
            $resultPayment = $this->doPayment($payment, $order);
            $helper = Mage::helper('mundipagg');
            $result = $helper->issetOr($resultPayment['result'], false);
            $ccResultCollection = $helper->issetOr(
                $result['CreditCardTransactionResultCollection']
            );

            if ($result === false) {
                return $this->integrationTimeOut($order, $payment);
            }

            // Return error
            if (isset($resultPayment['error'])) {
                return $this->paymentError($payment, $resultPayment);
            }

            if (is_null($ccResultCollection) === false) {
                // We record transaction(s)
                if (count($ccResultCollection) == 1) {
                    $trans = $ccResultCollection[0];
                    $this->_addTransaction(
                        $payment,
                        $trans['TransactionKey'],
                        Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                        $trans
                    );
                } else {
                    foreach ($ccResultCollection as $key => $trans) {
                        $this->_addTransaction(
                            $payment,
                            $trans['TransactionKey'],
                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                            $trans,
                            $key
                        );
                    }
                }
            }

            $statusWithError =
                Uecommerce_Mundipagg_Model_Enum_CreditCardTransactionStatusEnum::
                WITH_ERROR;
            $status = $trans['CreditCardTransactionStatus'];

            // Return
            if (isset($resultPayment['error'])) {
                try {
                    $payment->setSkipOrderProcessing(true)->save();

                    if (empty($resultPayment['ErrorDescription']) === false) {
                        Mage::throwException(Mage::helper('mundipagg')
                            ->__($resultPayment['ErrorDescription']));
                    }
                } catch (Exception $e) {
                    Mage::logException($e);

                    return $this;
                }
            } else {
                $accPaymentAuthorizationAmount = sprintf(
                    $order->getPaymentAuthorizationAmount()
                );
                $accGrandTotal = sprintf($order->getGrandTotal());

                // Send new order email when not in admin
                if (
                    Mage::app()->getStore()->getCode() != 'admin' &&
                    $accPaymentAuthorizationAmount == $accGrandTotal
                ) {
                    $order->sendNewOrderEmail();
                }

                $standardPaymentAction = Mage::getStoreConfig('payment/mundipagg_standard/payment_action');
                // We can capture only if:
                // 1. Multiple Credit Cards Payment
                // 2. Anti fraud is disabled
                // 3. Payment action is "AuthorizeAndCapture"
                // 4. Authorization amount is equal to grand_total
                if (
                    count($ccResultCollection) > 1 &&
                    $this->getAntiFraud() == 0 &&
                    $standardPaymentAction == 'order' &&
                    $accPaymentAuthorizationAmount == $accGrandTotal
                ) {
                    $this->captureAndcreateInvoice($payment);
                } elseif (
                    strtolower($status) == strtolower($statusWithError) ||
                    $this->isOfflineretry()
                ) {
                    $order->sendNewOrderEmail();
                    return $this;
                } elseif ($accPaymentAuthorizationAmount < $accGrandTotal) {
                    $order->cancel();
                    $order->setState(
                        Mage_Sales_Model_Order::STATE_CANCELED,
                        true
                    )->save();
                    $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);
                    $order->save();
                }
            }

            return $this;
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Capture payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return bool|Uecommerce_Mundipagg_Model_Standard
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $helper = Mage::helper('mundipagg');
        $post = Mage::app()->getRequest()->getPost();
        $captureCase = $helper->issetOr($post['invoice']['capture_case'], 'offline');

        if ($captureCase === 'online') {
            $this->captureOnline($payment);

            return $this;
        }

        if (!$this->canCapture()) {
            Mage::throwException($helper->__('Capture action is not available.'));
        }

        if ($payment->getAdditionalInformation('PaymentMethod') == 'mundipagg_boleto') {
            Mage::throwException($helper->__('You cannot capture Boleto Bancário.'));
        }

        if ($this->getAntiFraud() == 1) {
            Mage::throwException($helper->__('You cannot capture having anti fraud activated.'));
        }

        // Already captured
        if ($payment->getAdditionalInformation('CreditCardTransactionStatusEnum') == 'Captured' ||
            $payment->getAdditionalInformation('CreditCardTransactionStatus') == 'Captured'
        ) {
            return $this;
        }

        /* @var Mage_Sales_Model_Order_Payment $payment */
        $orderkeys = (array) $payment->getAdditionalInformation('OrderKey');

        if (empty($orderkeys)) {
            Mage::throwException(Mage::helper('mundipagg')->__('No OrderKey found.'));

            return false;
        }

        foreach ($orderkeys as $orderkey) {
            /* @var Uecommerce_Mundipagg_Model_Api $api */
            $api = Mage::getModel('mundipagg/api');
            //Call Gateway Api
            $capture = $api->saleCapture(
                ['OrderKey' => $orderkey],
                $payment->getOrder()->getIncrementId()
            );
            $ccTxnResultCollection = $helper->issetOr(
                $capture['CreditCardTransactionResultCollection']
            );

            if (!is_array($ccTxnResultCollection) ||
                is_null($ccTxnResultCollection) ||
                empty($ccTxnResultCollection)
            ) {
                Mage::helper('mundipagg/checkoutSession')
                    ->setApprovalRequestSuccess('cancel', $payment->getOrder());

                return false;
            }

            // Save transactions
            foreach ($ccTxnResultCollection as $txn) {
                $this->_addTransaction(
                    $payment,
                    $txn['TransactionKey'],
                    Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                    $txn
                );
            }
        }

        return true;
    }

    /**
     * Online capture payment abstract methodl
     *
     * @param Varien_Object $payment
     * @return void
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    public function captureOnline(Varien_Object $payment)
    {
        /* @var Uecommerce_Mundipagg_Helper_Data $helper */
        $helper = Mage::helper('mundipagg');

        if (!$this->canCapture()) {
            Mage::throwException($helper->__('Capture action is not available.'));
        }

        if ($payment->getAdditionalInformation('PaymentMethod') == 'mundipagg_boleto') {
            Mage::throwException(
                $helper->__('You cannot capture Boleto Bancário.')
            );
        }

        if ($this->getAntiFraud() == 1) {
            Mage::throwException(
                $helper->__('You cannot capture having anti fraud activated.')
            );
        }

        // Already captured
        if ($payment->getAdditionalInformation('CreditCardTransactionStatusEnum') == 'Captured' ||
            $payment->getAdditionalInformation('CreditCardTransactionStatus') == 'Captured'
        ) {
            Mage::throwException($helper->__('Transactions already captured'));
        }

        /* @var Mage_Sales_Model_Order_Payment $payment */
        $orderkeys = (array) $payment->getAdditionalInformation('OrderKey');

        if (empty($orderkeys)) {
            Mage::throwException(
                Mage::helper('mundipagg')->__('No OrderKey found.')
            );
        }

        $captureNotAllowedMsg = $helper->__('Capture was not authorized in MundiPagg');
        $txnsNotAuthorized = 0;

        foreach ($orderkeys as $orderkey) {
            $data['OrderKey'] = $orderkey;

            //Call Gateway Api
            /* @var Uecommerce_Mundipagg_Model_Api $api */
            $api = Mage::getModel('mundipagg/api');
            $capture = $api->saleCapture($data, $payment->getOrder()->getIncrementId());

            $ccTxnResultCollection = $helper->issetOr($capture['CreditCardTransactionResultCollection']);

            if (!is_array($ccTxnResultCollection) ||
                is_null($ccTxnResultCollection) ||
                empty($ccTxnResultCollection)
            ) {
                Mage::throwException($captureNotAllowedMsg);
            }

            $txnsNotAuthorized = 0;

            // Save transactions
            foreach ($ccTxnResultCollection as $txn) {
                $this->_addTransaction(
                    $payment,
                    $helper->issetOr($txn['TransactionKey']),
                    Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                    $txn
                );

                $success = $helper->issetOr($txn['Success'], false);

                if ($success === false) {
                    $txnsNotAuthorized++;
                }
            }
        }

        if ($txnsNotAuthorized === 1) {
            Mage::throwException($captureNotAllowedMsg);
        } elseif ($txnsNotAuthorized > 1) {
            Mage::throwException($helper->__('Capture partial authorized'));
        }

        $this->closeAuthorizationTxns($payment->getOrder());

        // if has just 1 invoice, update his grand total, adding the credit cards interests
        if (count($payment->getOrder()->getInvoiceCollection()) === 1) {
            /* @var Mage_Sales_Model_Order_Invoice $invoice */
            $invoice = $payment->getOrder()->getInvoiceCollection()->getItems()[0];
            $this->equalizeInvoiceTotals($invoice);
        }
    }

    public function closeAuthorizationTxns(Mage_Sales_Model_Order $order)
    {
        $txnsCollection = Mage::getModel('sales/order_payment_transaction')
                ->getCollection()
                ->addAttributeToFilter('order_id', ['eq' => $order->getId()]);

        /* @var Mage_Paypal_Model_Payment_Transaction $txn */
        foreach ($txnsCollection as $txn) {
            if ($txn->getTxnType() === 'authorization') {
                $txn->setOrderPaymentObject($order->getPayment());
                $txn->setIsClosed(true)->save();
            }
        }
    }

    /**
     * Capture payment abstract method
     *
     * @param Varien_Object $payment
     * @return Uecommerce_Mundipagg_Model_Standard
     * @throws Varien_Exception
     */
    public function captureAndcreateInvoice(Varien_Object $payment)
    {
        $order = $payment->getOrder();

        // Capture
        $capture = $this->capture($payment, $order->getGrandTotal());

        // Error
        if (!$capture) {
            Mage::helper('mundipagg/checkoutSession')
                ->setApprovalRequestSuccess('cancel', $order);

            return $this;
        }

        // Create invoice
        $invoice = Mage::getModel('sales/service_order', $order)
            ->prepareInvoice([]);
        $invoice->register();

        $invoice->setCanVoidFlag(true);
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->setState(2);

        if (Mage::helper('sales')->canSendNewInvoiceEmail($order->getStoreId())) {
            $invoice->setEmailSent(true);
            $invoice->sendEmail();
        }

        $invoice->save();

        $order->setBaseTotalPaid($order->getBaseGrandTotal());
        $order->setTotalPaid($order->getGrandTotal());
        $order->addStatusHistoryComment(
            'MP - Captured online amount of R$' . $order->getBaseGrandTotal(),
            false
        );
        $order->save();

        $this->closeAuthorizationTxns($order);
        $this->equalizeInvoiceTotals($invoice);

        return $this;
    }

    /**
     * Order payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    public function order(Varien_Object $payment, $amount)
    {
        if (!$this->canOrder()) {
            Mage::throwException(Mage::helper('payment')
                ->__('Order action is not available.'));
        }

        // Load order
        $order = $payment->getOrder();
        $order = Mage::getModel('sales/order')
            ->loadByIncrementId($order->getRealOrderId());

        $helperLog = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
        $helperLog->setLogLabel('|' . $order->getIncrementId() . '|');
        $helperLog->info('order()');

        // Proceed to payment on Gateway
        $resultPayment = $this->doPayment($payment, $order);

        $helper = Mage::helper('mundipagg');
        $result = $helper->issetOr($resultPayment['result'], false);
        $ccResultCollection = $helper->issetOr(
            $result['CreditCardTransactionResultCollection']
        );

        if ($result === false) {
            return $this->integrationTimeOut($order, $payment);
        }

        // Return error
        if (isset($resultPayment['error'])) {
            return $this->paymentError($payment, $resultPayment);
        }

        if (is_null($ccResultCollection) === false) {
            $status = [];

            foreach ($ccResultCollection as $collection) {
                $status[] = $collection['CreditCardTransactionStatus'];
            }

            $notAuthorized = in_array('NotAuthorized', $status);
            if ($this->isOfflineretry() && $notAuthorized){
                return $this;
            }
        }

        if (isset($resultPayment['message'])) {
            $transactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER;

            // Xml
            $xml = $resultPayment['result'];
            $json = json_encode($xml);

            $resultPayment['result'] = [];
            $resultPayment['result'] = json_decode($json, true);

            switch ($resultPayment['message']) {
                // Boleto
                case 0:
                    $boletoTransactionCollection = $helper->issetOr(
                        $resultPayment['result']['BoletoTransactionResultCollection'][0]
                    );

                    $boletoUrl = $helper->issetOr($boletoTransactionCollection['BoletoUrl']);

                    if (is_null($boletoUrl) === false) {
                        $payment->setAdditionalInformation('BoletoUrl', $boletoUrl);

                        // In order to show "Print Boleto" link in order email
                        $order->getPayment()->setAdditionalInformation(
                            'BoletoUrl',
                            $boletoUrl
                        );
                    }

                    $transactionKey = $helper->issetOr(
                        $boletoTransactionCollection['TransactionKey']
                    );
                    $this->_addTransaction(
                        $payment,
                        $transactionKey,
                        $transactionType,
                        $boletoTransactionCollection
                    );

                    // We record transaction(s)
                    if (count($resultPayment['result']['BoletoTransactionResultCollection']) == 1) {
                        $trans = $boletoTransactionCollection;
                        $this->_addTransaction(
                            $payment,
                            $trans['TransactionKey'],
                            $transactionType,
                            $trans
                        );
                    } else {
                        foreach ($boletoTransactionCollection as $key => $trans) {
                            $this->_addTransaction(
                                $payment,
                                $trans['TransactionKey'],
                                $transactionType,
                                $trans,
                                $key
                            );
                        }
                    }

                    $payment->setTransactionId($this->_transactionId);
                    $payment->save();

                    // Send new order email when not in admin
                    if (Mage::app()->getStore()->getCode() != 'admin') {
                        $order->sendNewOrderEmail();
                    }

                    break;

                // Credit Card
                case 1:
                    $this->orderCreditCard(
                        $order,
                        $result['CreditCardTransactionResultCollection'],
                        $payment,
                        $transactionType
                    );
                    break;

                // Debit
                case 4:
                    // We record transaction
                    $trans = $resultPayment['result'];

                    $this->_addTransaction(
                        $payment,
                        $trans['TransactionKey'],
                        $transactionType,
                        $trans
                    );
                    break;
            }
        }

        return $this;
    }

    /**
     * Proceed to payment
     * @param $payment
     * @param object $order
     * @return bool
     * @throws Varien_Exception
     */
    public function doPayment($payment, $order)
    {
        try {
            $helper = Mage::helper('mundipagg');
            $session = Mage::getSingleton('checkout/session');

            $mundipaggData = $session->getMundipaggData();
            $orderIncrementId = $order->getIncrementId();

            $logLabel = "Order #{$orderIncrementId}";

            $helperLog = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
            $helperLog->setLogLabel('|' . $order->getIncrementId() . '|');

            //Post data
            $postData = Mage::app()->getRequest()->getPost();

            // Get customer taxvat
            $taxvat = '';

            if ($order->getCustomerTaxvat() == '') {
                $customerId = $order->getCustomerId();

                if ($customerId) {
                    $customer = Mage::getModel('customer/customer')
                        ->load($customerId);
                    $taxvat = $customer->getTaxvat();
                }

                if ($taxvat != '') {
                    $order->setCustomerTaxvat($taxvat)->save();
                }
            } else {
                $taxvat = $order->getCustomerTaxvat();
            }

            // Data to pass to api
            $data['customer_id'] = $order->getCustomerId();
            $data['address_id'] = $order->getBillingAddress()
                ->getCustomerAddressId();

            $data['payment_method'] = $mundipaggData['method'];
            if (isset($postData['payment']['method'])) {
                $data['payment_method'] = $postData['payment']['method'];
            }

            $method = $data['payment_method'];

            $data = $this->formatPaymentRequest(
                $data,
                $method,
                $postData,
                $helper,
                $mundipaggData,
                $order,
                $taxvat
            );

            // Unset MundipaggData data
            $session->setMundipaggData();

            // Api
            $api = Mage::getModel('mundipagg/api');

            // Get approval request from gateway
            switch ($method) {
                case 'mundipagg_boleto':
                    $approvalRequest = $api->boletoTransaction(
                        $order,
                        $data,
                        $this
                    );
                    break;

                case 'mundipagg_debit':
                    $approvalRequest = $api->debitTransaction(
                        $order,
                        $data,
                        $this
                    );
                    break;

                case $method:
                    $approvalRequest = $api->creditCardTransaction(
                        $order,
                        $data,
                        $this
                    );
                    break;

                default:
                    $approvalRequest = false;
            }

            if ($approvalRequest === false) {
                return false;
            }

            // Set some data from Mundipagg
            $payment = $this->setPaymentAdditionalInformation(
                $approvalRequest,
                $payment
            );
            $authorizedAmount = $order->getPaymentAuthorizationAmount();

            if (is_null($authorizedAmount)) {
                $authorizedAmount = 0;
            }

            // Payment gateway error
            if (isset($approvalRequest['error'])) {
                if (isset($approvalRequest['ErrorItemCollection'])) {
                    $errorItemCollection = $approvalRequest['ErrorItemCollection'];

                    foreach ($errorItemCollection as $i) {
                        $errorCode = $helper->issetOr($i['ErrorCode']);

                        if ($errorCode == 504) {
                            $statusWithError = Uecommerce_Mundipagg_Model_Enum_CreditCardTransactionStatusEnum::WITH_ERROR;
                            Mage::helper('mundipagg/checkoutSession')
                                ->setApprovalRequestSuccess(
                                    $statusWithError,
                                    $order
                                );
                            Mage::getSingleton('checkout/session')
                                ->setWithErrorReason($errorCode = $helper->issetOr($i['Description'], 'With Error'));

                            return $approvalRequest;
                        }
                    }
                }

                if (isset($approvalRequest['ErrorCode']) &&
                    $approvalRequest['ErrorCode'] == 'multi'
                ) {
                    // Partial payment
                    // We set authorized amount
                    $orderGrandTotal = $order->getGrandTotal();

                    $approvalTransactionResult = $approvalRequest['result']
                            ->CreditCardTransactionResultCollection
                            ->CreditCardTransactionResult;

                    foreach ($approvalTransactionResult as $key => $result) {
                        if ($result->Success == true) {
                            $authorizedAmount +=
                                $result->AuthorizedAmountInCents * 0.01;
                        }
                    }

                    // If authorized amount is the same as order grand total
                    // we can show success page
                    $epsilon = 0.1;

                    if ($authorizedAmount != 0) {
                        if (($orderGrandTotal - $authorizedAmount) <= $epsilon) {
                            Mage::helper('mundipagg/checkoutSession')
                                ->setApprovalRequestSuccess('success', $order);
                            Mage::getSingleton('checkout/session')
                                ->setAuthorizedAmount();
                        } else {
                            Mage::helper('mundipagg/checkoutSession')
                                ->setApprovalRequestSuccess('partial', $order);
                            Mage::getSingleton('checkout/session')
                                ->setAuthorizedAmount($authorizedAmount);
                        }

                        $order->setPaymentAuthorizationAmount($authorizedAmount);
                        $order->save();
                    } else {
                        Mage::helper('mundipagg/checkoutSession')
                            ->setApprovalRequestSuccess('cancel', $order);
                    }
                } else {
                    $result = $helper->issetOr($approvalRequest['result'], false);

                    if ($result !== false) {
                        $action = 'cancel';

                        $status = $result['CreditCardTransactionResultCollection'][0]['CreditCardTransactionStatus'];
                        $statusWithError =
                            Uecommerce_Mundipagg_Model_Enum_CreditCardTransactionStatusEnum::
                            WITH_ERROR;

                        if (strtolower($status) == strtolower($statusWithError)) {
                            $msg = SELF::WITH_ERROR_MSG;
                            $action = $this->preventCancel($order, $msg);
                        }

                        if ($this->isOfflineretry()) {
                            $msg = SELF::OFFLINE_RETRY_MSG;
                            $action = $this->preventCancel($order, $msg);
                        }

                        Mage::helper('mundipagg/checkoutSession')
                            ->setApprovalRequestSuccess($action, $order);
                    }
                }

                return $approvalRequest;
            }

            switch ($approvalRequest['message']) {
                // BoletoBancario
                case 0:
                    Mage::helper('mundipagg/checkoutSession')
                        ->setApprovalRequestSuccess('success', $order);
                    break;

                // 1CreditCards
                case 1: // AuthAndCapture
                case 2: // AuthOnly
                case 3: // AuthAndCaptureWithDelay
                    // We set authorized amount in session
                    $orderGrandTotal = $order->getGrandTotal();
                    $result = $helper->issetOr($approvalRequest['result']);
                    $orderResult = $helper->issetOr($result['OrderResult']);
                    $creditCardTransactionResultCollection =
                        $result['CreditCardTransactionResultCollection'];
                    $transactionsQty = count($creditCardTransactionResultCollection);

                    if ($transactionsQty == 1) {
                        $transaction = $creditCardTransactionResultCollection[0];
                        $success = $transaction['Success'];

                        if ($success === true) {
                            $authorizedAmount +=
                                $transaction['AuthorizedAmountInCents'] * 0.01;
                        }
                    } else {
                        foreach ($creditCardTransactionResultCollection as $key => $transaction) {
                            $success = $transaction['Success'];

                            if ($success === true) {
                                $authorizedAmount +=
                                    $transaction['AuthorizedAmountInCents'] * 0.01;
                            } else {
                                $unauthorizedCreditCardMaskedNumber =
                                    $transaction['MaskedCreditCardNumber'];
                            }
                        }
                    }

                    // If authorized amount is the same as order grand total
                    // we can show success page
                    $epsilon = 0.1;

                    if (($orderGrandTotal - $authorizedAmount) <= $epsilon) {
                        Mage::helper('mundipagg/checkoutSession')
                            ->setApprovalRequestSuccess('success', $order);
                        Mage::getSingleton('checkout/session')
                            ->setAuthorizedAmount();

                        if ($orderGrandTotal < $authorizedAmount) {
                            $interestInformation = $payment
                                ->getAdditionalInformation(
                                    'mundipagg_interest_information'
                                );
                            $newInterestInformation = [];
                            $newInterest = 0;

                            if (count($interestInformation)) {
                                foreach ($interestInformation as $key => $ii) {
                                    $pos = strpos($key, 'partial');
                                    if ($pos !== false) {
                                        if ($ii->hasValue()) {
                                            $newInterest += (float) ($ii->getInterest());
                                        }
                                    }
                                }
                            }

                            $this->addInterestToOrder($order, $newInterest);
                        }
                    } else {
                        if ($authorizedAmount != 0) {
                            if (($orderGrandTotal - $authorizedAmount) >= $epsilon) {
                                Mage::helper('mundipagg/checkoutSession')
                                    ->setApprovalRequestSuccess('partial', $order);
                                Mage::getSingleton('checkout/session')
                                    ->setAuthorizedAmount($authorizedAmount);

                                $interestInformation = $payment
                                    ->getAdditionalInformation(
                                        'mundipagg_interest_information'
                                    );
                                $unauthorizedAmount =
                                    (float) ($orderGrandTotal - $authorizedAmount);
                                $newInterestInformation = [];

                                if (count($interestInformation)) {
                                    foreach ($interestInformation as $key => $ii) {
                                        if ($ii->hasValue()) {
                                            if ((float) ($ii->getValue() + $ii->getInterest()) == (float) trim($unauthorizedAmount)) {
                                                $this->removeInterestToOrder(
                                                    $order,
                                                    $ii->getInterest()
                                                );
                                            } else {
                                                $newInterestInformation[$key] = $ii;
                                            }
                                        } else {
                                            if (($order->getGrandTotal() + $order->getMundipaggInterest()) == $unauthorizedAmount) {
                                                $this->removeInterestToOrder(
                                                    $order,
                                                    $ii->getInterest()
                                                );
                                            } else {
                                                $newInterestInformation[$key] = $ii;
                                            }
                                        }
                                    }

                                    $payment->setAdditionalInformation(
                                        'mundipagg_interest_information',
                                        $newInterestInformation
                                    );
                                }
                            }
                        } else {
                            $result = $helper
                                ->issetOr($approvalRequest['result'], false);

                            if ($result !== false) {
                                $helperLog->info(
                                    "{$logLabel} | Payment not authorized order will be canceled."
                                );

                                $action = 'cancel';

                                $status = $result['CreditCardTransactionResultCollection'][0]['CreditCardTransactionStatus'];
                                $statusWithError =
                                    Uecommerce_Mundipagg_Model_Enum_CreditCardTransactionStatusEnum::
                                    WITH_ERROR;

                                if (strtolower($status) == strtolower($statusWithError)) {
                                    $msg = SELF::WITH_ERROR_MSG;
                                    $action = $this->preventCancel($order, $msg);
                                }

                                if ($this->isOfflineretry()) {
                                    $msg = SELF::OFFLINE_RETRY_MSG;
                                    $action = $this->preventCancel($order, $msg);
                                }

                                Mage::helper('mundipagg/checkoutSession')
                                    ->setApprovalRequestSuccess($action, $order);
                            }
                        }
                    }

                    $transactionKey = $transaction['TransactionKey'];
                    $creditCardTransactionStatusEnum =
                        $transaction['CreditCardTransactionStatus'];

                    try {
                        if ($transactionKey != null) {
                            $this->_transactionId = $transactionKey;

                            $payment->setTransactionId($this->_transactionId);
                            $payment->save();
                        }
                    } catch (Exception $e) {
                        $helperLog->error($e->getMessage());
                        continue;
                    }
                    break;

                // Debit
                case 4:
                    Mage::helper('mundipagg/checkoutSession')
                        ->setApprovalRequestSuccess('debit', $order);
                    Mage::getSingleton('checkout/session')
                        ->setBankRedirectUrl(
                            $approvalRequest['result']['BankRedirectUrl']
                        );
                    break;
            }

            $orderResult = $helper->issetOr($result['OrderResult']);

            if (isset($orderResult)) {
                $newOrderKey = $orderResult['OrderKey'];
                $orderPayment = $order->getPayment();
                $orderKeys = (array) $orderPayment
                    ->getAdditionalInformation('OrderKey');

                if (is_null($orderKeys) || !is_array($orderKeys)) {
                    $orderKeys = [];
                }

                if (!in_array($newOrderKey, $orderKeys)) {
                    $orderKeys[] = $newOrderKey;
                }

                $orderPayment->setAdditionalInformation(
                    'OrderKey',
                    $orderKeys
                );
                $orderPayment->save();
            }

            $order->setPaymentAuthorizationAmount($authorizedAmount);
            $order->save();

            if ($authorizedAmount == $order->getGrandTotal()) {
                Mage::helper('mundipagg/checkoutSession')
                    ->setApprovalRequestSuccess('success', $order);
            }

            return $approvalRequest;
        } catch (Exception $e) {
            //Api
            $api = Mage::getModel('mundipagg/api');

            //Log error
            Mage::logException($e);

            //Mail error
            $api->mailError(print_r($e->getMessage(), 1));
        }
    }

    /**
     * @param array $approvalRequest
     * @param       $payment
     * @return mixed
     */
    private function setPaymentAdditionalInformation($approvalRequest, $payment)
    {
        if (isset($approvalRequest['ErrorCode'])) {
            $payment->setAdditionalInformation(
                'ErrorCode',
                $approvalRequest['ErrorCode']
            );
        }

        if (isset($approvalRequest['ErrorDescription'])) {
            $payment->setAdditionalInformation(
                'ErrorDescription',
                $approvalRequest['ErrorDescription']
            );
        }

        if (isset($approvalRequest['OrderKey'])) {
            $payment->setAdditionalInformation(
                'OrderKey',
                $approvalRequest['OrderKey']
            );
        }

        if (isset($approvalRequest['OrderReference'])) {
            $payment->setAdditionalInformation(
                'OrderReference',
                $approvalRequest['OrderReference']
            );
        }

        if (isset($approvalRequest['CreateDate'])) {
            $payment->setAdditionalInformation(
                'CreateDate',
                $approvalRequest['CreateDate']
            );
        }

        if (isset($approvalRequest['OrderStatusEnum'])) {
            $payment->setAdditionalInformation(
                'OrderStatusEnum',
                $approvalRequest['OrderStatusEnum']
            );
        }

        if (isset($approvalRequest['TransactionKey'])) {
            $payment->setAdditionalInformation(
                'TransactionKey',
                $approvalRequest['TransactionKey']
            );
        }

        if (isset($approvalRequest['OnlineDebitStatus'])) {
            $payment->setAdditionalInformation(
                'OnlineDebitStatus',
                $approvalRequest['OnlineDebitStatus']
            );
        }

        if (isset($approvalRequest['TransactionKeyToBank'])) {
            $payment->setAdditionalInformation(
                'TransactionKeyToBank',
                $approvalRequest['TransactionKeyToBank']
            );
        }

        if (isset($approvalRequest['TransactionReference'])) {
            $payment->setAdditionalInformation(
                'TransactionReference',
                $approvalRequest['TransactionReference']
            );
        }

        if (array_key_exists('isRecurrency', $approvalRequest)) {
            $payment->setAdditionalInformation(
                'isRecurrency',
                $approvalRequest['isRecurrency']
            );
        }

        return $payment;
    }

    /**
     * Set capture transaction ID and enable Void to invoice for informational purposes
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function processInvoice($invoice, $payment)
    {
        if ($payment->getLastTransId()) {
            $invoice->setTransactionId($payment->getLastTransId());
            $invoice->setCanVoidFlag(true);

            if (Mage::helper('sales')->canSendNewInvoiceEmail($payment->getOrder()->getStoreId())) {
                $invoice->setEmailSent(true);
                $invoice->sendEmail();
            }

            return $this;
        }

        return false;
    }

    /**
     * Check void availability
     *
     * @return bool
     */
    public function canVoid(Varien_Object $payment)
    {
        if ($payment instanceof Mage_Sales_Model_Order_Creditmemo) {
            return false;
        }

        return $this->_canVoid;
    }

    public function void(Varien_Object $payment)
    {
        if (!$this->canVoid($payment)) {
            Mage::throwException(Mage::helper('payment')
                ->__('Void action is not available.'));
        }

        //Prepare data in order to void
        if ($payment->getAdditionalInformation('OrderKey')) {
            $transactions =
                Mage::getModel('sales/order_payment_transaction')
                    ->getCollection()
                    ->addAttributeToFilter(
                        'order_id', ['eq' => $payment->getOrder()->getEntityId()]
                    );

            foreach ($transactions as $key => $transaction) {
                $TransactionKey = $transaction
                    ->getAdditionalInformation('TransactionKey');
                $TransactionReference = $transaction
                    ->getAdditionalInformation('TransactionReference');
            }

            $orderkeys = $payment->getAdditionalInformation('OrderKey');

            if (!is_array($orderkeys)) {
                $orderkeys = [$orderkeys];
            }

            foreach ($orderkeys as $orderkey) {
                $data['ManageOrderOperationEnum'] = 'Cancel';
                $data['OrderKey'] = $orderkey;

                //Call Gateway Api
                $api = Mage::getModel('mundipagg/api');

                $void = $api->manageOrderRequest($data, $this);

                // Xml
                $xml = $void['result'];
                $json = json_encode($xml);

                $void['result'] = [];
                $void['result'] = json_decode($json, true);

                // We record transaction(s)
                if (count($void['result']['CreditCardTransactionResultCollection']) > 0) {
                    if (count($xml->CreditCardTransactionResultCollection->CreditCardTransactionResult) == 1) {
                        $trans = $void['result']['CreditCardTransactionResultCollection']['CreditCardTransactionResult'];

                        $this->_addTransaction(
                            $payment,
                            $trans['TransactionKey'],
                            'void',
                            $trans
                        );
                    } else {
                        foreach ($void['result']['CreditCardTransactionResultCollection']['CreditCardTransactionResult'] as $key => $trans) {
                            $this->_addTransaction($payment,
                                $trans['TransactionKey'],
                                'void',
                                $trans,
                                $key
                            );
                        }
                    }
                }

                if (isset($void['result']['CreditCardTransactionResultCollection']['CreditCardTransactionResult'])) {
                    $order = $payment->getOrder();
                    $order->setBaseDiscountRefunded($order->getBaseDiscountInvoiced());
                    $order->setBaseShippingRefunded($order->getBaseShippingAmount());
                    $order->setBaseShippingTaxRefunded($order->getBaseShippingTaxInvoiced());
                    $order->setBaseSubtotalRefunded($order->getBaseSubtotalInvoiced());
                    $order->setBaseTaxRefunded($order->getBaseTaxInvoiced());
                    $order->setBaseTotalOnlineRefunded($order->getBaseGrandTotal());
                    $order->setDiscountRefunded($order->getDiscountInvoiced());
                    $order->setShippinRefunded($order->getShippingInvoiced());
                    $order->setShippinTaxRefunded($order->getShippingTaxAmount());
                    $order->setSubtotalRefunded($order->getSubtotalInvoiced());
                    $order->setTaxRefunded($order->getTaxInvoiced());
                    $order->setTotalOnlineRefunded($order->getBaseGrandTotal());
                    $order->setTotalRefunded($order->getBaseGrandTotal());
                    $order->save();

                    return $this;
                } else {
                    $error = Mage::helper('mundipagg')
                        ->__('Unable to void order.');

                    //Log error
                    Mage::log($error, null, 'Uecommerce_Mundipagg.log');

                    Mage::throwException($error);
                }
            }
        } else {
            Mage::throwException(Mage::helper('mundipagg')
                ->__('No OrderKey found.'));
        }
    }

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
        return $this->_canRefund;
    }

    /**
     * Set refund transaction id to payment object for informational purposes
     * Candidate to be deprecated:
     * there can be multiple refunds per payment, thus payment.refund_transactionId doesn't make big sense
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Payment_Model_Method_Abstract
     * @throws Varien_Exception
     */
    public function processBeforeRefund($invoice, $payment)
    {
        $payment->setRefundTransactionId($invoice->getTransactionId());

        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Uecommerce_Mundipagg_Model_Standard
     * @throws Mage_Core_Exception
     * @throws Varien_Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        if (!$this->canRefund()) {
            Mage::throwException(Mage::helper('payment')
                ->__('Refund action is not available.'));
        }

        //Prepare data in order to refund
        if ($payment->getAdditionalInformation('OrderKey')) {
            $data['OrderKey'] = $payment->getAdditionalInformation(
                'OrderKey'
            );

            $data['ManageOrderOperationEnum'] = 'Void';

            //Call Gateway Api
            $api = Mage::getModel('mundipagg/api');

            $refund = $api->manageOrderRequest($data, $this);

            // Xml
            $xml = $refund['result'];
            $json = json_encode($xml);

            $refund['result'] = [];
            $refund['result'] = json_decode($json, true);

            // We record transaction(s)
            if (count($refund['result']['CreditCardTransactionResultCollection']) > 0) {
                if (count($xml->CreditCardTransactionResultCollection->CreditCardTransactionResult) == 1) {
                    $trans = $refund['result']['CreditCardTransactionResultCollection']['CreditCardTransactionResult'];

                    $this->_addTransaction(
                        $payment,
                        $trans['TransactionKey'],
                        'void',
                        $trans
                    );
                } else {
                    foreach ($refund['result']['CreditCardTransactionResultCollection']['CreditCardTransactionResult'] as $key => $trans) {
                        $this->_addTransaction(
                            $payment,
                            $trans['TransactionKey'],
                            'void',
                            $trans,
                            $key
                        );
                    }
                }
            }

            if (isset($xml->CreditCardTransactionResultCollection->CreditCardTransactionResult)) {
                if (count($xml->CreditCardTransactionResultCollection->CreditCardTransactionResult) == 1) {
                    $capturedAmountInCents = $manageOrderResult
                        ->CreditCardTransactionResultCollection
                        ->CreditCardTransactionResult
                        ->CapturedAmountInCents;
                } else {
                    $capturedAmountInCents = 0;

                    foreach ($refund['result']['CreditCardTransactionResultCollection']['CreditCardTransactionResult'] as $key => $trans) {
                        $capturedAmountInCents += $trans['CapturedAmountInCents'];
                    }
                }

                $order = $payment->getOrder();
                $order->setBaseDiscountRefunded($order->getBaseDiscountInvoiced());
                $order->setBaseShippingRefunded($order->getBaseShippingAmount());
                $order->setBaseShippingTaxRefunded($order->getBaseShippingTaxInvoiced());
                $order->setBaseSubtotalRefunded($order->getBaseSubtotalInvoiced());
                $order->setBaseTaxRefunded($order->getBaseTaxInvoiced());
                $order->setBaseTotalOnlineRefunded($capturedAmountInCents * 0.01);
                $order->setDiscountRefunded($order->getDiscountInvoiced());
                $order->setShippinRefunded($order->getShippingInvoiced());
                $order->setShippinTaxRefunded($order->getShippingTaxAmount());
                $order->setSubtotalRefunded($order->getSubtotalInvoiced());
                $order->setTaxRefunded($order->getTaxInvoiced());
                $order->setTotalOnlineRefunded($capturedAmountInCents * 0.01);
                $order->setTotalRefunded($capturedAmountInCents * 0.01);
                $order->save();

                return $this;
            } else {
                $error = Mage::helper('mundipagg')
                    ->__('Unable to refund order.');

                //Log error
                Mage::log($error, null, 'Uecommerce_Mundipagg.log');

                Mage::throwException($error);
            }
        } else {
            Mage::throwException(Mage::helper('mundipagg')
                ->__('No OrderKey found.'));
        }
    }


    private function isValidCVVOnInstantBuy($info)
    {
        $paymentMethod = $info->getAdditionalInformation('PaymentMethod');
        $paymentType = $info->getAdditionalInformation('mundipagg_type');
        if ($paymentMethod !== 'mundipagg_creditcard' &&
            $paymentMethod !== 'mundipagg_twocreditcards'
        ) {
            return true;
        }

        if (Mage::getStoreConfig('payment/mundipagg_standard/ask_cvv_cardonfile')) {
            switch ($paymentType) {
                case '1CreditCards':
                    $cvv = null;
                    $token = $info->getAdditionalInformation(
                        'mundipagg_creditcard_token_1_1'
                    );
                    $countCcs = $info->getAdditionalInformation('CountCcs');

                    $newCard = $token === 'new';
                    $savedCard = $token === '1';

                    // if card is new OR card is saved and has cvv
                    if ($newCard || ($savedCard && $cvv) || $countCcs >= 0) {
                        return true;
                    }

                    return false;

                case '2CreditCards':
                    $totalSavedCardsUsed = 0;

                    $cvv1 = $cvv2 = null;

                    $token1 = $info->getAdditionalInformation(
                        'mundipagg_twocreditcards_token_2_1'
                    );
                    $token2 = $info->getAdditionalInformation(
                        'mundipagg_twocreditcards_token_2_2'
                    );

                    $savedCard1 = $token1 === '1';
                    $savedCard2 = $token2 === '1';

                    // how much save
                    if ($savedCard1) {
                        $totalSavedCardsUsed++;
                    }

                    if ($savedCard2) {
                        $totalSavedCardsUsed++;
                    }

                    if ($totalSavedCardsUsed === 0) {
                        return true;
                    } elseif ($totalSavedCardsUsed === 1) {
                        if ($savedCard1) {
                            return !empty($token1);
                        } elseif ($savedCard2) {
                            return !empty($token2);
                        }
                    } elseif ($totalSavedCardsUsed === 2) {
                        return $cvv1 && $cvv2;
                    }
            }
        }

        return true;
    }


    /**
     * Validate
     */
    public function validate()
    {
        parent::validate();

        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

        if (!in_array($currencyCode, $this->_allowCurrencyCode)) {
            Mage::throwException(
                Mage::helper('payment')
                    ->__(
                        'Selected currency code (' .
                        $currencyCode .
                        ') is not compatabile with Mundipagg'
                    )
            );
        }

        $info = $this->getInfoInstance();

        $errorMsg = [];

        if (!$this->isValidCVVOnInstantBuy($info)) {
            $errorMsg[] = Mage::helper('payment')->__('Informe o CVV');
        }

        // Check if we are dealing with a new Credit Card
        $isToken = $info->getAdditionalInformation('mundipagg_creditcard_token_1_1');

        if ($info->getAdditionalInformation('PaymentMethod') == 'mundipagg_creditcard' &&
            ($isToken == '' || $isToken == 'new')
        ) {
            $availableTypes = $this->getCcTypes();

            $ccNumber = $info->getCcNumber();

            // refresh quote to remove promotions from others payment methods
            try {
                $this->getQuote()->save();
            } catch (Exception $e) {
                $errorMsg[] = $e->getMessage();
            }

            // remove credit card number delimiters such as "-" and space
            $ccNumber = preg_replace(
                '/[\-\s]+/', '',
                $ccNumber
            );
            $info->setCcNumber($ccNumber);

            if (in_array($info->getCcType(), $availableTypes)) {
                if (!Mage::helper('mundipagg')->validateCcNum($ccNumber) &&
                    $info->getCcType() != 'HI'
                ) {
                    $errorMsg[] = Mage::helper('payment')
                        ->__('Invalid Credit Card Number');
                }
            } else {
                $errorMsg[] = Mage::helper('payment')
                    ->__('Credit card type is not allowed for this payment method.');
            }

            if (!$info->getCcType()) {
                $errorMsg[] = Mage::helper('payment')
                    ->__('Please select your credit card type.');
            }

            if (!$info->getCcOwner()) {
                $errorMsg[] = Mage::helper('payment')
                    ->__('Please enter your credit card holder name.');
            }

            if ($info->getCcType() && $info->getCcType() != 'SS' &&
                !Mage::helper('mundipagg')->validateExpDate('20' . $info->getCcExpYear(), $info->getCcExpMonth())
            ) {
                $errorMsg[] = Mage::helper('payment')
                    ->__('Incorrect credit card expiration date.');
            }
        }

        if ($info->getAdditionalInformation('PaymentMethod') !== 'mundipagg_creditcardoneinstallment' &&
            !$this->validateInstallments($info)) {
            $errorMsg[] = Mage::helper('payment')
                ->__('Invalid installments number');
        }

        if ($errorMsg) {
            $json = json_encode($errorMsg);
            Mage::throwException($json);
        }

        return $this;
    }

    private function validateInstallments($info)
    {
        $paymentMethod = $info->getAdditionalInformation('PaymentMethod');
        if ($paymentMethod === 'mundipagg_boleto') {
            return true;
        }

        $installments = $info->getAdditionalInformation('mundipagg_installments');
        foreach ($installments as $installmentsByCard) {
            if (!$installmentsByCard) {
                return false;
            }
        }

        return true;
    }

    /**
     * Redirect Url
     *
     * @return void
     * @throws Varien_Exception
     */
    public function getOrderPlaceRedirectUrl()
    {
        $statusWithError = Uecommerce_Mundipagg_Model_Enum_CreditCardTransactionStatusEnum::WITH_ERROR;

        switch (Mage::getSingleton('checkout/session')->getApprovalRequestSuccess()) {
            case 'debit':
            case 'success':
                $redirectUrl = Mage::getUrl(
                    'mundipagg/standard/success',
                    ['_secure' => true]
                );
                break;

            case $statusWithError:
                $redirectUrl = Mage::getUrl(
                    'mundipagg/standard/success',
                    ['_secure' => true]
                );
                break;

            case 'partial':
                $redirectUrl = Mage::getUrl(
                    'mundipagg/standard/partial',
                    ['_secure' => true]
                );
                break;

            case 'cancel':
                $redirectUrl = Mage::getUrl(
                    'mundipagg/standard/cancel',
                    ['_secure' => true]
                );
                break;

            default:
                $redirectUrl = Mage::getUrl(
                    'mundipagg/standard/cancel',
                    ['_secure' => true]
                );
                break;
        }

        return $redirectUrl;
    }

    public function prepare()
    {
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods()
    {
        $payment_methods = $this->getConfigData('payment_methods');

        if ($payment_methods != '') {
            $payment_methods = explode(",", $payment_methods);
        } else {
            $payment_methods = [];
        }

        return $payment_methods;
    }

    /**
     * CCards
     */
    public function getCcTypes()
    {
        $ccTypes = Mage::getStoreConfig('payment/mundipagg_standard/cc_types');

        if ($ccTypes != '') {
            $ccTypes = explode(",", $ccTypes);
        } else {
            $ccTypes = [];
        }

        return $ccTypes;
    }

    /**
     * Reset interest
     */
    public function resetInterest($info)
    {
        if (
            $info->getQuote()->getMundipaggInterest() > 0 ||
            $info->getQuote()->getMundipaggBaseInterest() > 0
        ) {
            $info->getQuote()->setMundipaggInterest(0.0);
            $info->getQuote()->setMundipaggBaseInterest(0.0);
            $info->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        }

        return $info;
    }

    /**
     * Apply interest
     */
    public function applyInterest($info, $interest)
    {
        $info
            ->getQuote()
            ->setMundipaggInterest(
                $info->getQuote()->getStore()->convertPrice($interest, false)
            );
        $info->getQuote()->setMundipaggBaseInterest($interest);
        return $info;
    }

    /**
     * Remove interest to order when the total is not allowed.
     *
     * @param Mage_Sales_Model_Order $order
     * @param float                  $interest
     */
    protected function removeInterestToOrder(
        Mage_Sales_Model_Order $order,
        $interest
    ) {
        $mundipaggInterest = $order->getMundipaggInterest();
        $setInterest = (float) ($mundipaggInterest - $interest);
        $order->setMundipaggInterest(($setInterest) ? $setInterest : 0);
        $order->setMundipaggBaseInterest(($setInterest) ? $setInterest : 0);
        $order->setGrandTotal(($order->getGrandTotal() - $interest));
        $order->setBaseGrandTotal(($order->getBaseGrandTotal() - $interest));
        $order->save();
        $info = $this->getInfoInstance();
        $info->setPaymentInterest(($info->getPaymentInterest() - $setInterest));
        $info->save();
    }

    /**
     * Add interest to order
     */
    protected function addInterestToOrder(Mage_Sales_Model_Order $order, $interest)
    {
        $mundipaggInterest = $order->getMundipaggInterest();
        $setInterest = (float) ($mundipaggInterest + $interest);
        $order->setMundipaggInterest(($setInterest) ? $setInterest : 0);
        $order->setMundipaggBaseInterest(($setInterest) ? $setInterest : 0);
        $order->setGrandTotal(($order->getGrandTotal() + $interest));
        $order->setBaseGrandTotal(($order->getBaseGrandTotal() + $interest));
        $order->save();
    }

    /**
     * Add payment transaction
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string                         $transactionId
     * @param string                         $transactionType
     * @param array                          $transactionAdditionalInfo
     * @return null|Mage_Sales_Model_Order_Payment_Transaction
     */
    public function _addTransaction(
        Mage_Sales_Model_Order_Payment $payment,
        $transactionId,
        $transactionType,
        $transactionAdditionalInfo,
        $num = 0
    ) {
        // Num
        $num = $num + 1;

        // Transaction
        $transaction = Mage::getModel('sales/order_payment_transaction');
        $transaction->setOrderPaymentObject($payment);

        $transaction = $transaction->loadByTxnId($transactionId . '-' . $transactionType);

        $transaction->setTxnType($transactionType);
        $transaction->setTxnId($transactionId . '-' . $transactionType);

        if ($transactionType == 'authorization') {
            $ccTransactionStatus = $transactionAdditionalInfo['CreditCardTransactionStatus'];
            $transactionOpenStatuses = [
                'AuthorizedPendingCapture',
                'Captured',
                'PartialCapture',
                'WithError',
                'PendingAuthorize'
            ];

            $order = $payment->getOrder();
            $orderIncrementId = $order->getIncrementId();

            $api = new Uecommerce_Mundipagg_Model_Api();

            if (in_array($ccTransactionStatus, $transactionOpenStatuses)) {
                $transaction->setIsClosed(0);
            } else {
                $transaction->setIsClosed(1);
            }
        }

        foreach ($transactionAdditionalInfo as $transKey => $value) {
            if (!is_array($value)) {
                $transaction->setAdditionalInformation(
                    $transKey,
                    htmlspecialchars_decode($value)
                );
                $payment->setAdditionalInformation(
                    $num . '_' . $transKey, htmlspecialchars_decode($value)
                );
            } else {
                if (empty($value)) {
                    $transaction->setAdditionalInformation($transKey, '');
                    $payment->setAdditionalInformation($num . '_' . $transKey, '');
                } else {
                    foreach ($value as $key2 => $value2) {
                        $transaction->setAdditionalInformation(
                            $key2,
                            htmlspecialchars_decode($value2)
                        );
                        $payment->setAdditionalInformation(
                            $num . '_' . $key2, htmlspecialchars_decode($value2)
                        );

                        if ($key2 === 'InstantBuyKey') {
                            $api = Mage::getModel('mundipagg/api');
                            $holderName = $api->getHolderNameByInstantBuyKey($value2);

                            $payment->setAdditionalInformation(
                                $num . '_HolderName',
                                htmlspecialchars_decode($holderName)
                            );
                        }
                    }
                }
            }
        }

        return $transaction->save();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    public static function transactionWithError(
        Mage_Sales_Model_Order $order,
        $comment = true
    ) {
        try {
            $msg = Mage::getSingleton('checkout/session')
                ->getWithErrorReason();

            if ($comment) {
                $order->setState(
                    'pending',
                    'pending',
                    'MP - ' . $msg ,
                    false
                );
            } else {
                $order->setStatus('pending');
            }

            $order->save();
        } catch (Exception $e) {
            $errMsg = "Unable to modify order status to 'mundipagg_with_error: {$e->getMessage()}";

            throw new Exception($errMsg);
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param                                $resultPayment
     * @return $this
     */
    private function paymentError(
        Mage_Sales_Model_Order_Payment $payment,
        $resultPayment
    ) {
        try {
            $transactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER;
            $helper = Mage::helper('mundipagg');
            $result = $helper->issetOr($resultPayment['result']);
            $ccTxnCollection = $helper->issetOr($result['CreditCardTransactionResultCollection']);

            // We record transaction(s)
            if (is_null($ccTxnCollection) === false) {
                if (count($ccTxnCollection) == 1) {
                    $trans = $ccTxnCollection[0];

                    $this->_addTransaction(
                        $payment,
                        $trans['TransactionKey'],
                        $transactionType, $trans
                    );
                } else {
                    foreach ($ccTxnCollection as $key => $trans) {
                        $this->_addTransaction(
                            $payment,
                            $trans['TransactionKey'],
                            $transactionType,
                            $trans,
                            $key
                        );
                    }
                }
            }

            if (isset($resultPayment['ErrorItemCollection'])) {
                if (count($resultPayment['ErrorItemCollection']) == 1) {
                    foreach ($resultPayment['ErrorItemCollection']['ErrorItem'] as $key => $value) {
                        $payment->setAdditionalInformation($key, $value)->save();
                    }
                } else {
                    foreach ($resultPayment['ErrorItemCollection'] as $key1 => $error) {
                        foreach ($error as $key2 => $value) {
                            $payment->setAdditionalInformation(
                                $key1 . '_' . $key2,
                                $value
                            )->save();
                        }
                    }
                }
            }

            $payment->setSkipOrderProcessing(true)->save();

            if (isset($resultPayment['ErrorDescription'])) {
                Mage::throwException(Mage::helper('mundipagg')
                    ->__($resultPayment['ErrorDescription']));
            } else {
                Mage::throwException(Mage::helper('mundipagg')
                    ->__('Error'));
            }
        } catch (Exception $e) {
            return $this;
        }
    }

    private function integrationTimeOut(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Payment &$payment)
    {
        $payment->setSkipOrderProcessing(true);
        $payment->setAdditionalInformation(
            'IntegrationError',
            Uecommerce_Mundipagg_Model_Api::INTEGRATION_TIMEOUT
        );

        $comment = "MP - " . Uecommerce_Mundipagg_Model_Api::INTEGRATION_TIMEOUT;
        $order->addStatusHistoryComment($comment);
        $order->save();

        Mage::helper('mundipagg/checkoutSession')->setApprovalRequestSuccess('success', $order);

        return $this;
    }

    /**
     * @param array                          $mundiQueryResult
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return bool|null
     */
    public function processQueryResults(
        $mundiQueryResult,
        Mage_Sales_Model_Order_Payment $payment
    ) {
        $helper = Mage::helper('mundipagg');
        $log = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);

        $order = $payment->getOrder();
        $log->setLogLabel("#{$order->getIncrementId()}");

        $saleDataCollection = $helper->issetOr(
            $mundiQueryResult['SaleDataCollection']
        );

        if (is_null($saleDataCollection)) {
            $log->info("SaleDataCollection is null. Method execution is over");

            return false;
        }

        $saleData = null;
        $dateFormat = 'Y-m-d';

        foreach ($saleDataCollection as $i) {
            $createDate = $i['OrderData']['CreateDate'];
            $transactionCreateDate = new DateTime($createDate);
            $orderCreateDate = new DateTime($order->getCreatedAt());

            $formatTransDate = $transactionCreateDate->format($dateFormat);
            $formatOrderDate = $orderCreateDate->format($dateFormat);

            if ($formatTransDate == $formatOrderDate) {
                $saleData = $i;
                continue;
            }
        }

        $creditCardTransactionDataCollection = $helper->issetOr(
            $saleData['CreditCardTransactionDataCollection']
        );

        if (is_null($creditCardTransactionDataCollection)) {
            $log->info(
                "CreditCardTransactionDataCollection is null. Method execution is over"
            );

            return false;
        }

        $transactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;

        foreach ($creditCardTransactionDataCollection as $i) {
            $transactionId = $i['TransactionKey'];
            $this->_addTransaction(
                $payment,
                $transactionId,
                $transactionType,
                $i
            );
        }

        return true;
    }

    public function removeIntegrationErrorInfo(Mage_Sales_Model_Order $order)
    {
        $errMsg = null;
        $log = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
        $log->setLogLabel("#{$order->getIncrementId()}");

        $info = $order->getPayment()->getAdditionalInformation();

        if (!isset($info['IntegrationError'])) {
            return;
        }

        try {
            $info = $order->getPayment()->getAdditionalInformation();

            unset($info['IntegrationError']);

            $order->getPayment()
                    ->setAdditionalInformation($info)
                    ->save();

            $log->info("IntegrationError message removed");
        } catch (Exception $e) {
            $log->error($e, true);
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param boolean $option
     * @throws Mage_Core_Exception
     */
    public function setCanceledByNotificationFlag(&$order, $option)
    {
        $order->getPayment()->setAdditionalInformation(
            'voided_by_mundi_notification', $option
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array|mixed|null
     */
    public function getCanceledByNotificationFlag($order)
    {
        return $order->getPayment()->getAdditionalInformation(
            'voided_by_mundi_notification'
        );
    }

    /**
     * Equalize invoice base_grand_total and base_total with order totals
     * Needed when order has 1 invoice and has credit card interests
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @throws Exception
     */
    public function equalizeInvoiceTotals(Mage_Sales_Model_Order_Invoice &$invoice)
    {
        $invoice->setBaseGrandTotal($invoice->getOrder()->getBaseGrandTotal())
                ->setGrandTotal($invoice->getOrder()->getGrandTotal())
                ->save();
    }

    /**
     * @param Mage_Checkout_Model_Type_Onepage $onepage
     * @param array                            $postData
     * @return null|string $redirectRoute
     * @throws Exception
     */
    public function retryAuthorization(
        Mage_Checkout_Model_Type_Onepage &$onepage,
        $postData
    ) {
        $redirectRoute = null;

        /* @var Uecommerce_Mundipagg_Helper_CheckoutSession $session */
        $session = Mage::helper('mundipagg/checkoutSession');
        $lastQuoteId = $session->getInstance()->getLastSuccessQuoteId();
        $session->getInstance()->setQuoteId($lastQuoteId);

        /* @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
        $quote->setIsActive(true);

        $onepage->setQuote($quote);

        // Get Reserved Order Id
        $reservedOrderId = $quote->getReservedOrderId();

        if ($reservedOrderId == false) {
            return $redirectRoute;
        }

        Mage::helper('mundipagg/checkoutSession')->setApprovalRequest('partial');

        $order = Mage::getModel('sales/order')
            ->loadByIncrementId($reservedOrderId);

        //
        $additionalInfo = $order->getPayment()->getAdditionalInformation();
        $num = 1;

        if ($additionalInfo['2_Success']) {
            $num++;
        }

        $idxToken = "mundipagg_twocreditcards_token_2_{$num}";

        switch (true) {
            case isset($additionalInfo[$idxToken]):
                $order->getPayment()->setAdditionalInformation();
                break;
        }

        /* @var Uecommerce_Mundipagg_Helper_Data $helper */
        $helper = Mage::helper('mundipagg');

        if ($order->getStatus() === 'pending' ||
            $order->getStatus() === 'payment_review'
        ) {
            if (empty($postData)) {
                throw new Exception($helper->__('Invalid data'));
            }

            $paymentMethod = $helper->issetOr($postData['method']);

            if ($quote->isVirtual()) {
                $quote->getBillingAddress()->setPaymentMethod($paymentMethod);
            } else {
                $quote->getShippingAddress()->setPaymentMethod($paymentMethod);
            }

            $payment = $quote->getPayment();
            $payment->importData($postData);

            $quote->save();

            switch ($paymentMethod) {
                case 'mundipagg_creditcardoneinstallment':
                    $standard = Mage::getModel(
                        'mundipagg/creditcardoneinstallment'
                    );
                    break;
                case 'mundipagg_creditcard':
                    $standard = Mage::getModel(
                        'mundipagg/creditcard'
                    );
                    break;

                case 'mundipagg_twocreditcards':
                    $standard = Mage::getModel(
                        'mundipagg/twocreditcards'
                    );
                    break;

                case 'mundipagg_recurrencepayment':
                    $standard = Mage::getModel(
                        'mundipagg/recurrencepayment'
                    );
                    break;

                default:
                    return 'mundipagg/standard/partial';
                    break;
            }

            /* @var Uecommerce_Mundipagg_Model_Standard $standard */
            $resultPayment = $standard->doPayment($payment, $order);
            $txns = $helper->issetOr(
                $resultPayment['result']['CreditCardTransactionResultCollection']
            );

            foreach ($txns as $txn) {
                $standard->_addTransaction(
                    $order->getPayment(),
                    $helper->issetOr($txn['TransactionKey']),
                    Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                    $txn,
                    $num - 1
                );
            }

            $order->getPayment()->save();

//			$this->replaceNotAuthorizedCcInfo($txns[0], $order->getPayment());

            $accAuthorizedAmount = sprintf(
                $order->getData('payment_authorization_amount')
            );
            $accGrandTotal = sprintf($order->getData('grand_total'));

            if ($standard->getAntiFraud() == 0 &&
                $standard->getPaymentAction() === 'order' &&
                $accAuthorizedAmount == $accGrandTotal
            ) {
                $standard->captureAndcreateInvoice($order->getPayment());
            }

            switch ($session->getApprovalRequest()) {
                case 'success':
                    // Send new order email when not in admin and payment is success
                    if (Mage::app()->getStore()->getCode() !== 'admin') {
                        $order->sendNewOrderEmail();
                    }
                    $redirectRoute = 'mundipagg/standard/success';
                    break;

                case 'partial':
                    $redirectRoute = 'mundipagg/standard/partial';
                    break;

                case 'cancel':
                    $redirectRoute = 'mundipagg/standard/cancel';
                    break;

                default:
                    throw new Exception(
                        "Unexpected approvalRequestSuccess: {$session->getApprovalRequest()}"
                    );
            }
        }

        return $redirectRoute;
    }

    /**
     * Replace the not authorized payment additional information
     */
    public function replaceNotAuthorizedCcInfo(
        $mundiResponse,
        Mage_Sales_Model_Order_Payment &$payment
    ) {
        $info = $payment->getAdditionalInformation();
        $ccQty = $info['mundipagg_type'][0];
        $keys = array_keys($info);
        $ccsData = [];
        $otherData = [];

        // separate credit cards payment additional information
        foreach ($keys as $key) {
            $idxTwoInitialLetters = $key[0] . $key[1];
            $value = $info[$key];

            if ($idxTwoInitialLetters === '1_') {
                $ccsData[1][$key] = $value;
            } elseif ($idxTwoInitialLetters === '2_') {
                $ccsData[2][$key] = $value;
            } else {
                $otherData[$key] = $value;
            }
        }

        $notAuthorizedCc = null;

        // get just the not authorized credit card data
        for ($i = 1; $i <= $ccQty; $i++) {
            $idx = "{$i}_Success";
            $success = $ccsData[$i][$idx];

            if ($success) {
//				$notAuthorizedCc = $i === 1 ? 1 : 2;
                if ($i === 1) {
                    $notAuthorizedCc = 1;
                } else {
                    $notAuthorizedCc = 2;
                }
                break;
            }
        }

        $responseKeys = $this->extractTxnKeys($mundiResponse);
        $key = null;

        foreach ($responseKeys as $key => $val) {
            $idx = "{$notAuthorizedCc}_{$val}";
            $ccsData[$idx] = isset($ccsData[$idx]) ? $ccsData[$idx] : null;
        }

        $data = $otherData + $ccsData[1] + $ccsData[2];

        $payment->setAdditionalInformation($data)->save();
    }

    public function extractTxnKeys($txn)
    {
        $keys = [];

        foreach ($txn as $key => $val) {
            if (is_array($txn[$key]) && !empty($txn[$key])) {
                foreach ($txn[$key] as $subKey => $subVal) {
                    $keys[] = $subKey;
                }
            } else {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Save Mundipagg payment data in the database
     * @param array $mundipagg
     * @param Mage_Payment_Model_Info $info
     * @throws Mage_Core_Exception
     */
    private function saveCreditCardAdditionalInformation($mundipagg, $info)
    {
        $method = $mundipagg['method'];
        if (!empty($mundipagg[$method . '_1_1_cc_type'])) {
            $this->blockNotAllowedInstallments($mundipagg);

            $info->setCcType($mundipagg[$method . '_1_1_cc_type'])
                ->setCcOwner($mundipagg[$method . '_cc_holder_name_1_1'])
                ->setCcLast4(substr($mundipagg[$method . '_1_1_cc_number'], -4))
                ->setCcNumber($mundipagg[$method . '_1_1_cc_number'])
                ->setCcCid($mundipagg[$method . '_cc_cid_1_1'])
                ->setCcExpMonth($mundipagg[$method . '_expirationMonth_1_1'])
                ->setCcExpYear($mundipagg[$method . '_expirationYear_1_1']);
        } else {
            $info->setAdditionalInformation(
                $method . '_token_1_1',
                $mundipagg[$method . '_token_1_1']
            );

            if (isset($mundipagg[$method . '_card_on_file_cvv_1_1'])) {
                $info->setAdditionalInformation(
                    $mundipagg[$method . '_card_on_file_cvv_1_1']
                );
            }
        }
    }

    /**
     * Block script execution when installments number
     * exists and is highest than max installments number
     * @param array $mundipagg
     * @return bool
     * @throws Mage_Core_Exception
     */
    private function blockNotAllowedInstallments($mundipagg)
    {
        if (array_key_exists('mundipagg_creditcard_credito_parcelamento_1_1', $mundipagg)) {
            if ($mundipagg['mundipagg_creditcard_credito_parcelamento_1_1'] >
                $mundipagg['mundipagg_creditcard_1_1_cc_type_max_installments']
            ) {
                Mage::throwException(
                    "Installments number not allowed. \n" .
                    "Insallments selected:" .
                    $mundipagg['mundipagg_creditcard_credito_parcelamento_1_1'] .
                    "\n" .
                    "Max installents allowed: " .
                    $mundipagg['mundipagg_creditcard_1_1_cc_type_max_installments']
                );
            }
        }
        return true;
    }

    /**
     * @param $mundipagg
     * @param $info
     * @param $helper
     * @return void
     */
    private function saveAllAdditionalInformation($mundipagg, $info, $helper)
    {
        foreach ($mundipagg as $key => $value) {
            // We don't save CcNumber
            $posCcNumber = strpos($key, 'number');

            // We don't save Security Code
           // Save temporarily for recurring
+          //  $posCid = strpos($key, 'cid');
            $posCid = false;


            // We don't save Cc Holder name
            $posHolderName = strpos($key, 'holder_name');

            if ($value != '' &&
                $posCcNumber === false &&
                $posCid === false &&
                $posHolderName === false
            ) {
                $pos = strpos($key, 'cc_type');
                if ($pos) {
                    $value = $helper->issuer($value);
                }

                $info->setAdditionalInformation($key, $value);
            }
        }
    }
    /**
     * We check if quote grand total is equal to installments sum
     * @param array $mundipagg
     * @param $info
     * @param $helper
     * @throws Mage_Core_Exception
     */
    private function validateInstallmentsAmount(
        $mundipagg,
        $info,
        $helper,
        $helperInstallments
    ) {
        if (
            $mundipagg['method'] != 'mundipagg_boleto' &&
            $mundipagg['method'] != 'mundipagg_creditcardoneinstallment' &&
            $mundipagg['method'] != 'mundipagg_creditcard'
        ) {
            $num = $helper->getCreditCardsNumber($mundipagg['method']);
            $method = $helper->getPaymentMethod($num);

            (float) $grandTotal = $info->getQuote()->getGrandTotal();
            (float) $totalInstallmentsToken = 0;
            (float) $totalInstallmentsNew = 0;
            (float) $totalInstallments = 0;

            for ($i = 1; $i <= $num; $i++) {
                if (isset($mundipagg[$method . '_token_' . $num . '_' . $i]) &&
                    $mundipagg[$method . '_token_' . $num . '_' . $i] != 'new'
                ) {
                    (float) $value = str_replace(
                        ',',
                        '.',
                        $mundipagg[$method . '_value_' . $num . '_' . $i]
                    );

                    if (array_key_exists($method . '_credito_parcelamento_' . $num . '_' . $i, $mundipagg)) {
                        if (!array_key_exists($method . '_' . $num . '_' . $i . '_cc_type', $mundipagg)) {
                            $cardonFile = Mage::getModel('mundipagg/cardonfile')
                                ->load($mundipagg[$method . '_token_' . $num . '_' . $i]);

                            $mundipagg[$method . '_' . $num . '_' . $i . '_cc_type'] =
                                Mage::helper('mundipagg')
                                    ->getCardTypeByIssuer($cardonFile->getCcType());
                        }

                        if (
                            $mundipagg[$method . '_credito_parcelamento_' . $num . '_' . $i] >
                            $helperInstallments->getMaxInstallments(
                                $mundipagg[$method . '_' . $num . '_' . $i . '_cc_type'],
                                $value
                            )
                        ) {
                            Mage::throwException($helper
                                ->__(
                                    'it is not possible to divide by %s times',
                                    $mundipagg[$method . '_credito_parcelamento_' . $num . '_' . $i]
                                )
                            );
                        }
                    }

                    (float) $totalInstallmentsToken += $value;
                } else {
                    (float) $value = str_replace(
                        ',',
                        '.',
                        $mundipagg[$method . '_new_value_' . $num . '_' . $i]
                    );

                    if (array_key_exists($method . '_new_credito_parcelamento_' . $num . '_' . $i, $mundipagg)) {
                        if ($mundipagg[$method . '_new_credito_parcelamento_' . $num . '_' . $i] >
                            $helperInstallments->getMaxInstallments(
                                $mundipagg[$method . '_' . $num . '_' . $i . '_cc_type'],
                                $value
                            )
                        ) {
                            Mage::throwException($helper->__(
                                'it is not possible to divide by %s times',
                                $mundipagg[$method . '_new_credito_parcelamento_' . $num . '_' . $i]
                            ));
                        }
                    }

                    (float) $totalInstallmentsNew += $value;
                }
            }

            // Total Installments from token and Credit Card
            (float) $totalInstallments =
                $totalInstallmentsToken + $totalInstallmentsNew;

            // If an amount has already been authorized$helperInstallments = Mage::helper('mundipagg/Installments');
            if (isset($mundipagg['multi']) &&
                Mage::getSingleton('checkout/session')->getAuthorizedAmount()
            ) {
                (float) $totalInstallments += (float) Mage::getSingleton(
                    'checkout/session'
                )->getAuthorizedAmount();

                // Unset session
                Mage::getSingleton('checkout/session')
                    ->setAuthorizedAmount();
            }
        }
    }

    private function doCreditCardsPayment(
        $method,
        $postData,
        $helper,
        $mundipaggData,
        $order,
        $taxvat
    ) {
        $num = $helper->getCreditCardsNumber($method);

        if ($num == 0) {
            $num = 1;
        }
        if ($num > 1) {
            $method = $helper->getPaymentMethod($num);
        }

        for ($i = 1; $i <= $num; $i++) {
            // New Credit Card
            if (!isset($postData['payment'][$method . '_token_' . $num . '_' . $i]) ||
                (isset($postData['payment'][$method . '_token_' . $num . '_' . $i]) &&
                    $postData['payment'][$method . '_token_' . $num . '_' . $i] == 'new'
                )
            ) {
                if (isset($postData['payment'][$method . '_cc_holder_name_' . $num . '_' . $i])) {
                    $data['payment'][$i]['HolderName'] =
                        $postData['payment'][$method . '_cc_holder_name_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['HolderName'] =
                        $mundipaggData[$method . '_cc_holder_name_' . $num . '_' . $i];
                }

                if (isset($postData['payment'][$method . '_' . $num . '_' . $i . '_cc_number'])) {
                    $data['payment'][$i]['CreditCardNumber'] =
                        $postData['payment'][$method . '_' . $num . '_' . $i . '_cc_number'];
                } else {
                    $data['payment'][$i]['CreditCardNumber'] =
                        $mundipaggData[$method . '_' . $num . '_' . $i . '_cc_number'];
                }

                if (isset($postData['payment'][$method . '_expirationMonth_' . $num . '_' . $i])) {
                    $data['payment'][$i]['ExpMonth'] =
                        $postData['payment'][$method . '_expirationMonth_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['ExpMonth'] =
                        $mundipaggData[$method . '_expirationMonth_' . $num . '_' . $i];
                }

                if (isset($postData['payment'][$method . '_expirationYear_' . $num . '_' . $i])) {
                    $data['payment'][$i]['ExpYear'] =
                        $postData['payment'][$method . '_expirationYear_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['ExpYear'] =
                        $mundipaggData[$method . '_expirationYear_' . $num . '_' . $i];
                }

                if (isset($postData['payment'][$method . '_cc_cid_' . $num . '_' . $i])) {
                    $data['payment'][$i]['SecurityCode'] =
                        $postData['payment'][$method . '_cc_cid_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['SecurityCode'] =
                        $mundipaggData[$method . '_cc_cid_' . $num . '_' . $i];
                }

                if (Mage::helper('mundipagg')->issuer(isset($postData['payment'][$method . '_' . $num . '_' . $i . '_cc_type']))) {
                    $data['payment'][$i]['CreditCardBrandEnum'] =
                        $this->brands[$postData['payment'][$method . '_' . $num . '_' . $i . '_cc_type']];
                } else {
                    $data['payment'][$i]['CreditCardBrandEnum'] =
                        $this->brands[$mundipaggData[$method . '_' . $num . '_' . $i . '_cc_type']];
                }

                if (isset($postData['payment'][$method . '_new_credito_parcelamento_' . $num . '_' . $i])) {
                    $data['payment'][$i]['InstallmentCount'] =
                        $postData['payment'][$method . '_new_credito_parcelamento_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['InstallmentCount'] = 1;
                }

                if (isset($postData['payment'][$method . '_save_token_' . $num . '_' . $i])) {
                    $data['payment'][$i]['token'] =
                        $postData['payment'][$method . '_save_token_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['token'] = null;
                }

                $new = $method . '_new_value_' . $num . '_' . $i;
                if (isset($postData['payment'][$new]) &&
                    $postData['payment'][$new] != ''
                ) {
                    $data['payment'][$i]['AmountInCents'] = str_replace(
                        ',',
                        '.',
                        $postData['payment'][$new]
                    );

                    if (isset($postData['payment'][$method . '_' . $num . '_' . $i . '_cc_type'])) {
                        $cctype =
                            $postData['payment'][$method . '_' . $num . '_' . $i . '_cc_type'];
                    } else {
                        $cctype =
                            $mundipaggData[$method . '_' . $num . '_' . $i . '_cc_type'];
                    }

                    $interest =
                        Mage::helper('mundipagg/installments')
                            ->getInterestForCard(
                                $data['payment'][$i]['InstallmentCount'],
                                $cctype,
                                $data['payment'][$i]['AmountInCents']
                            );

                    $amountInCents =
                        $data['payment'][$i]['AmountInCents'] + $interest;
                    $data['payment'][$i]['AmountInCents'] =
                        $amountInCents * 100;
                } else {
                    if (!isset($postData['partial'])) {
                        $data['payment'][$i]['AmountInCents'] = $order->getGrandTotal() * 100;
                    } else { // If partial payment we deduct authorized amount already processed
                        if (Mage::getSingleton('checkout/session')->getAuthorizedAmount()) {
                            $data['payment'][$i]['AmountInCents'] =
                                ($order->getGrandTotal()) * 100 -
                                Mage::getSingleton('checkout/session')->getAuthorizedAmount() * 100;
                        } else {
                            $data['payment'][$i]['AmountInCents'] = ($order->getGrandTotal()) * 100;
                        }
                    }
                }

                if (isset($postData['payment'][$method . '_cc_taxvat_' . $num . '_' . $i])) {
                    $data['payment'][$i]['TaxDocumentNumber'] =
                        $postData['payment'][$method . '_cc_taxvat_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['TaxDocumentNumber'] = $taxvat;
                }
            } else { // Token
                if (isset($postData['payment'][$method . '_card_on_file_cvv_' . $num . '_' . $i])) {
                    $data['payment'][$i]['cvv'] =
                        $postData['payment'][$method . '_card_on_file_cvv_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['HolderName'] = '';
                }

                if (isset($postData['payment'][$method . '_token_' . $num . '_' . $i])) {
                    $data['payment'][$i]['card_on_file_id'] =
                        $postData['payment'][$method . '_token_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['card_on_file_id'] =
                        $mundipaggData[$method . '_token_' . $num . '_' . $i];
                }

                if (isset($postData['payment'][$method . '_credito_parcelamento_' . $num . '_' . $i])) {
                    $data['payment'][$i]['InstallmentCount'] =
                        $postData['payment'][$method . '_credito_parcelamento_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['InstallmentCount'] = 1;
                }

                if (isset($postData['payment'][$method . '_value_' . $num . '_' . $i]) &&
                    $postData['payment'][$method . '_value_' . $num . '_' . $i] != ''
                ) {
                    $data['payment'][$i]['AmountInCents'] = str_replace(
                        ',',
                        '.', $postData['payment'][$method . '_value_' . $num . '_' . $i]
                    );
                    $cardonFile = Mage::getModel('mundipagg/cardonfile')
                        ->load($postData['payment'][$method . '_token_' . $num . '_' . $i]);
                    $tokenCctype = Mage::getSingleton('mundipagg/source_cctypes')
                        ->getCcTypeForLabel($cardonFile->getCcType());
                    $data['payment'][$i]['AmountInCents'] =
                        $data['payment'][$i]['AmountInCents'] +
                        Mage::helper('mundipagg/installments')
                            ->getInterestForCard(
                                $data['payment'][$i]['InstallmentCount'],
                                $tokenCctype,
                                $data['payment'][$i]['AmountInCents']
                            );
                    $data['payment'][$i]['AmountInCents'] =
                        $data['payment'][$i]['AmountInCents'] * 100;
                } else {
                    if (!isset($postData['partial'])) {
                        $data['payment'][$i]['AmountInCents'] =
                            $order->getGrandTotal() * 100;
                    } else { // If partial payment we deduct authorized amount already processed
                        if (Mage::getSingleton('checkout/session')->getAuthorizedAmount()) {
                            $data['payment'][$i]['AmountInCents'] =
                                ($order->getGrandTotal()) * 100 -
                                Mage::getSingleton('checkout/session')
                                    ->getAuthorizedAmount() * 100;
                        } else {
                            $data['payment'][$i]['AmountInCents'] =
                                $order->getGrandTotal() * 100;
                        }
                    }
                }

                if (isset($postData['payment'][$method . '_cc_taxvat_' . $num . '_' . $i])) {
                    $data['payment'][$i]['TaxDocumentNumber'] =
                        $postData['payment'][$method . '_cc_taxvat_' . $num . '_' . $i];
                } else {
                    $data['payment'][$i]['TaxDocumentNumber'] = $taxvat;
                }
            }

            if (Mage::helper('mundipagg')->validateCPF($data['payment'][$i]['TaxDocumentNumber'])
            ) {
                $data['PersonTypeEnum'] = 'Person';
                $data['TaxDocumentTypeEnum'] = 'CPF';
                $data['TaxDocumentNumber'] = $data['payment'][$i]['TaxDocumentNumber'];
            }

            // We verify if a CNPJ is informed
            if (Mage::helper('mundipagg')
                ->validateCNPJ($data['payment'][$i]['TaxDocumentNumber'])
            ) {
                $data['PersonTypeEnum'] = 'Company';
                $data['TaxDocumentTypeEnum'] = 'CNPJ';
                $data['TaxDocumentNumber'] = $data['payment'][$i]['TaxDocumentNumber'];
            }

            $data['payment'][$i]['InstallmentCount'] =
                $this->getInstallmentsNumber(
                    $postData['payment'],
                    $method,
                    $num,
                    $i
                );
        }

        return $data;
    }

    private function  getInstallmentsNumber($paymentData, $method, $ccQty, $ccPos)
    {
        $realCcPos = $ccQty == 1 ? 1 : $ccPos;

        $savedCreditCardSelector = $method . '_token_' . $ccQty . '_' . $realCcPos;

        $new = '';
        if (
            empty($paymentData[$savedCreditCardSelector]) ||
            $paymentData[$savedCreditCardSelector] == 'new'
        ) {
            $new = '_new';
        }

        $index = "{$method}{$new}_credito_parcelamento_{$ccQty}_{$realCcPos}";

        if (!isset($paymentData[$index])) {
            return 1;
        }

        return $paymentData[$index];
    }

    private function doBoletoPayment($data, $postData, $taxvat)
    {
        $data['TaxDocumentNumber'] =
            isset($postData['payment']['boleto_taxvat']) ?
                $postData['payment']['boleto_taxvat'] :
                $taxvat;
        $data['boleto_parcelamento'] =
            isset($postData['payment']['boleto_parcelamento']) ?
                $postData['payment']['boleto_parcelamento'] : 1;
        $data['boleto_dates'] = isset($postData['payment']['boleto_dates']) ?
            $postData['payment']['boleto_dates'] :
            null;

        // We verify if a CPF is informed
        if (Mage::helper('mundipagg')->validateCPF($data['TaxDocumentNumber'])) {
            $data['PersonTypeEnum'] = 'Person';
            $data['TaxDocumentTypeEnum'] = 'CPF';
        }

        // We verify if a CNPJ is informed
        if (Mage::helper('mundipagg')->validateCNPJ($data['TaxDocumentNumber'])) {
            $data['PersonTypeEnum'] = 'Company';
            $data['TaxDocumentTypeEnum'] = 'CNPJ';
        }

        return $data;
    }

    private function doDebitPayment($data, $postData, $mundipaggData, $taxvat)
    {
        $data['TaxDocumentNumber'] =
            isset($postData['payment']['taxvat']) ?
                $postData['payment']['taxvat'] :
                $taxvat;
        $data['Bank'] =
            isset($postData['payment']['mundipagg_debit']) ?
                $postData['payment']['mundipagg_debit'] :
                $mundipaggData['mundipagg_debit'];

        // We verify if a CPF is informed
        if (Mage::helper('mundipagg')->validateCPF($data['TaxDocumentNumber'])) {
            $data['PersonTypeEnum'] = 'Person';
            $data['TaxDocumentTypeEnum'] = 'CPF';
        }

        // We verify if a CNPJ is informed
        if (Mage::helper('mundipagg')->validateCNPJ($data['TaxDocumentNumber'])) {
            $data['PersonTypeEnum'] = 'Company';
            $data['TaxDocumentTypeEnum'] = 'CNPJ';
        }

        return $data;
    }

    private function clearCart()
    {
        $cart = Mage::getModel('checkout/cart');
        $cart->truncate()->save(); // remove all active items in cart page
        $cart->init();
        $session= Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();
        $cart = Mage::getModel('checkout/cart');
        $cartItems = $cart->getItems();
        foreach ($cartItems as $item) {
            $quote->removeItem($item->getId())->save();
        }
        Mage::getSingleton('checkout/session')->clear();
    }

    private function formatPaymentRequest(
        $data,
        $method,
        $postData,
        $helper,
        $mundipaggData,
        $order,
        $taxvat
    ) {
        $formattedData = [];

        // 1 or more Credit Cards Payment
        if ($method != 'mundipagg_boleto' && $method != 'mundipagg_debit') {
            $formattedData = $this->doCreditCardsPayment(
                $method,
                $postData,
                $helper,
                $mundipaggData,
                $order,
                $taxvat
            );
        }

        // Boleto Payment
        if ($method == 'mundipagg_boleto') {
            $formattedData = $this->doBoletoPayment($data, $postData, $taxvat);
        }

        // Debit Payment
        if ($method == 'mundipagg_debit') {
            $formattedData = $this->doDebitPayment(
                $data,
                $postData,
                $mundipaggData,
                $taxvat
            );
        }

        return array_merge($data, $formattedData);
    }

    private function orderCreditCard(
        $order,
        $creditCardTransactionResultCollection,
        $payment,
        $transactionType
    ) {
        // We record transaction(s)
        $this->recordTransactions(
            $creditCardTransactionResultCollection,
            $payment,
            $transactionType
        );

        // Send new order email when not in admin
        if (Mage::app()->getStore()->getCode() != 'admin') {
            $order->sendNewOrderEmail();
        }

        $order = $payment->getOrder();
        $this->createInvoice($order, $payment);

        $order->setBaseTotalPaid($order->getBaseGrandTotal());
        $order->setTotalPaid($order->getGrandTotal());
        $order->addStatusHistoryComment(
            'MP - Captured online amount of R$' . $order->getBaseGrandTotal(),
            'Pending'
        );

        $payment->setLastTransId($this->_transactionId);
        $payment->save();
    }

    private function recordTransactions($transactions, $payment, $transactionType)
    {
        foreach ($transactions as $key => $trans) {
            if (array_key_exists('TransactionKey', $trans)) {
                $this->_addTransaction(
                    $payment,
                    $trans['TransactionKey'],
                    $transactionType,
                    $trans,
                    $key
                );
            }
        }
    }

    private function createInvoice($order, $payment)
    {
        $invoice = $this->registerInvoice($order, $payment);

        if ($invoice) {
            $this->captureInvoice($invoice);
            $this->sendInvoiceMail($invoice, $order->getStoreId());
            $invoice->save();
        }
    }

    private function registerInvoice($order, $payment)
    {
        if (!$order->canInvoice()) {
            // Log error
            Mage::logException(Mage::helper('core')->__('Cannot create an invoice.'));
            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
            return false;
        }
        $invoice = Mage::getModel(
            'sales/service_order',
            $payment->getOrder()
        )->prepareInvoice([]);
        $invoice->register();
        return $invoice;
    }

    private function captureInvoice($invoice)
    {
        $invoice->setTransactionId($this->_transactionId);
        $invoice->setCanVoidFlag(true);
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->setState(2);
    }

    private function sendInvoiceMail($invoice, $storeId)
    {
        // Send invoice if enabled
        if (Mage::helper('sales')->canSendNewInvoiceEmail($storeId)) {
            $invoice->setEmailSent(true);
            $invoice->sendEmail();
        }
    }

    private function isOfflineretry()
    {
        return Mage::getStoreConfig(
            'payment/mundipagg_standard/offline_retry_enabled'
        );
    }

    private function preventCancel($order, $msg)
    {
        $helperLog = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);

        try {
            $logLabel = 'Order #' . $order->getRealOrderId();
            $helperLog->info($logLabel . $msg);

            $order->addStatusHistoryComment('MP - ' . $msg, false);
            $order->save();

            return 'success';

        } catch (Exception $e) {
            $helperLog->error($e->getMessage());
        }
    }


    private function checkCpf($index, $value)
    {
        // We verify if a CPF OR CNPJ is valid
        $posTaxvat = strpos($index, 'taxvat');

        if ($posTaxvat !== false && $value != '') {
            $helper = Mage::helper('mundipagg');

            if (!$helper->validateCPF($value) && !$helper->validateCNPJ($value)) {
                $error = $helper->__('CPF or CNPJ is invalid');
                Mage::throwException($error);
            }
        }
    }
}
