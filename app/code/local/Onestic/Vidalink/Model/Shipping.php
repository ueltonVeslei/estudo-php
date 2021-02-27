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
class Onestic_Vidalink_Model_Shipping extends Mage_Shipping_Model_Carrier_Abstract {

    protected $_code = 'shipping';
    
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        $shippingConfiguration = Mage::getSingleton("onestic_vidalink/shipping_configuration");
        if (!$shippingConfiguration->getIsActive()) {
            return;
        }
        
        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier('vidalink');
        $method->setCarrierTitle('Vidalink');
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