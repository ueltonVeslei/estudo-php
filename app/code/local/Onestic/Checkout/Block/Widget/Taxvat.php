<?php
class Onestic_Checkout_Block_Widget_Taxvat extends Mage_Customer_Block_Widget_Taxvat
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('onestic/checkout/widget/taxvat.phtml');
    }
}
