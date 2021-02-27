<?php

/**
 *
 * @category   Onestic
 * @package    Onestic_Checkout
 * @author     Suporte <suporte@onestic.com>
 */

class Onestic_Checkout_Block_Widget_Dob extends Mage_Customer_Block_Widget_Dob
{
  protected $_dateInputs = array();
  
  public function _construct()
  {
    parent::_construct();
    $this->setTemplate('onestic/checkout/widget/dob.phtml');
  }
}
