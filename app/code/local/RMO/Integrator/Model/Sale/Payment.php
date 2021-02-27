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


/**
 * This class represents the payment method that will be used only by  orders
 * exported from the skyhub system into the magento system. It must not be available
 * to the magento end-users in the front-end.
 * 
 */
class RMO_Integrator_Model_Sale_Payment extends Mage_Payment_Model_Method_Abstract {
    
    protected $_code = 'skyhub_payment';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_canUseCheckout          = false;
    protected $_canCapture              = true;


   /*
    * Method added just to be compatible with the Adyen Payment module which has
    * an observer that calls this method when the order is canceled.
    *
    * For more info see:
    *  https://github.com/Adyen/magento/commit/05adaf0e53eb2abbf0f29c5a93221f77431e462d
    *  http://answers.magentocommerce.com/answers/4643-en_us/product/22803/adyen-adyen-payment-questions-answers/questions.htm?sort=helpfula
    *
    */
    public function SendCancelOrRefund($param1 = nil, $param2 = nil, $param3 = nil, $param4 = nil) {

    }
}

