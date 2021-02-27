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
class Onestic_Vidalink_Model_Payment extends Mage_Payment_Model_Method_Abstract {
    
    protected $_code = 'vidalink_payment';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_canUseCheckout          = false;
    protected $_canCapture              = true;


    public function SendCancelOrRefund($param1 = nil, $param2 = nil, $param3 = nil, $param4 = nil) {

    }
}

