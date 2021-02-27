<?php
class Onestic_Checkout_Block_Widget_Name extends Mage_Customer_Block_Widget_Name
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('onestic/checkout/widget/name.phtml');
    }
}
