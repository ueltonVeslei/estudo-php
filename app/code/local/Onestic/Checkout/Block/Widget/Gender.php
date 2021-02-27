<?php
class Onestic_Checkout_Block_Widget_Gender extends Mage_Customer_Block_Widget_Gender
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('onestic/checkout/widget/gender.phtml');
    }
}
