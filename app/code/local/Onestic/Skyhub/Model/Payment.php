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
class Onestic_Skyhub_Model_Payment extends Mage_Payment_Model_Method_Abstract {
    
    protected $_code = 'skyhub_payment';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_canUseCheckout          = false;
    protected $_canCapture              = true;
    protected $_infoBlockType = 'onestic_skyhub/payment_info';


    public function SendCancelOrRefund($param1 = nil, $param2 = nil, $param3 = nil, $param4 = nil) {

    }
    
    public function setCode($channel) {
        $this->_code = strtolower($channel);
    }
}

