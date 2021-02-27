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
 * To be able to import the orders from the Skyhub system into the Magento, it will be
 * necessary to specify the shipping method. For that, we can not use the shipping methods
 * that are available to the magento end-users in the front-end due to the following reasons:
 *  
 * (1) There might be a mismatch between the shipping methods used in the skyhub system and 
 *     the ones used in the Magento system.
 * 
 * (2) In the shipping methods as the (brazilian )Correios, the price and delivery date estimation 
 * are calculate for each request. The problem is that if the correios webservice is down,
 * the Magento system will not be able to import the orders from Skyhub.
 * 
 * To avoid those problems, it is necessary to have a special shipping method that can be used
 * only by this module. 
 */
 
class RMO_Integrator_Model_Sale_Shipping extends Mage_Shipping_Model_Carrier_Abstract {

    protected $_code = 'skyhub';
    
    /**
     * Returns the shippings rates of this shipping method.
     * 
     * This function depends heavily on the singleton model:
     * 
     * $shippingConfiguration = Mage::getSingleton("rmointegrator/sale_shipping_configuration")
     * 
     * if $shippingConfiguration->getIsActive() returns true, than this function will return
     * the shipping rates and delivery estimation according to the values set on the others parameters of 
     * the $shippingConfiguration object. It  returns false, otherwise.
     * 
     * Therefore, the parameters values of the singleton "rmointegrator/sale_shipping_configuration" must be filled in
     * with the shipping information from the Skyhub System. These values needs to be set for each order to be imported.
     * 
     * @param Mage_Shipping_Model_Rate_Request $request
     * @see RMO_Integrator_Model_Sale_Shipping_Configuration
     * @return type
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        $shippingConfiguration = Mage::getSingleton("rmointegrator/sale_shipping_configuration");
        if (!$shippingConfiguration->getIsActive()) {
            return;
        }
        
        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier('skyhub');
        $method->setCarrierTitle('Skyhub');
        $method->setMethod($shippingConfiguration->getShippingMethodCode());
        $method->setMethodTitle($shippingConfiguration->getShippingMethodName());
        $method->setPrice($shippingConfiguration->getShippingPrice());
        $method->setCost($shippingConfiguration->getShippingPrice());
        $result->append($method);
        return $result;
    }
    
    public function getAllowedMethods() {
        return array($this->_code => $this->getConfigData('name'));
    }
}