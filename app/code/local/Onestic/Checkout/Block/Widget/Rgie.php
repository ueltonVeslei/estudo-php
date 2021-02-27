<?php

/**
 *
 * @category   Onestic
 * @package    Onestic_Checkout
 * @author     Suporte <suporte@onestic.com>
 */

class Onestic_Checkout_Block_Widget_Rgie extends Mage_Customer_Block_Widget_Abstract 
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('onestic/checkout/widget/rgie.phtml');
	}

    public function isEnabled()
    {
  		return (bool)$this->_getAttribute('rgie')->getIsVisible();
    }

    public function isRequired()
    {
   		return (bool)$this->_getAttribute('rgie')->getIsRequired();
    }

    public function getCustomer()
    {
    	return Mage::getSingleton('customer/session')->getCustomer();
    }
}
