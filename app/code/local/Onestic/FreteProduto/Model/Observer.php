<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Onestic
 * @package    Onestic_FreteProduto
 * @copyright  Copyright (c) 2017 Ecommerce Developer Blog (http://www.onestic.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Module observer
 *
 */
class Onestic_FreteProduto_Model_Observer
{
    /**
     * Config model
     *
     * @var Onestic_FreteProduto_Model_Config
     */
    protected $_config = null;

    /**
     * Retrieve configuration model for module
     *
     * @return Onestic_FreteProduto_Model_Config
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = Mage::getSingleton('onestic_freteproduto/config');
        }

        return $this->_config;
    }

    /**
     * Layouts initializations observer,
     * add the form block into the position that was specified by the configuration
     *
     * @param Varien_Event_Observer $observer
     */
    public function observeLayoutHandleInitialization(Varien_Event_Observer $observer)
    {
        /* @var $controllerAction Mage_Core_Controller_Varien_Action */
        $controllerAction = $observer->getEvent()->getAction();
        $fullActionName = $controllerAction->getFullActionName();
        if ($this->getConfig()->isEnabled() && in_array($fullActionName, $this->getConfig()->getControllerActions())) {
            if ($this->getConfig()->getDisplayPosition() === Onestic_FreteProduto_Model_Config::DISPLAY_POSITION_LEFT) {
                // Display the form in the left column on the page
                $controllerAction->getLayout()->getUpdate()->addHandle(
                    Onestic_FreteProduto_Model_Config::LAYOUT_HANDLE_LEFT
                );
            } elseif ($this->getConfig()->getDisplayPosition() === Onestic_FreteProduto_Model_Config::DISPLAY_POSITION_RIGHT) {
                // Display the form in the right column on the page
                $controllerAction->getLayout()->getUpdate()->addHandle(
                    Onestic_FreteProduto_Model_Config::LAYOUT_HANDLE_RIGHT
                );
            }
        }
    }
    
    public function addPostcode($observer) {
    	$formvalues = Mage::getSingleton('onestic_freteproduto/session')->getFormValues();
    	if (isset($formvalues['postcode'])) {
	    	$cart = Mage::getSingleton('checkout/cart');
	    	$address = $cart->getQuote()->getShippingAddress();
	    	if (!$address->getPostcode()) {
		    	$address->setCountryId('BR')
		    		->setPostcode($formvalues['postcode'])
		    		->setCollectShippingrates(true);
		    	//$cart->save();
	    	}
    	}
    }
}

