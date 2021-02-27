<?php
class Onestic_Skyhub_Block_Payment_Info extends Mage_Payment_Block_Info
{
    protected function _construct ()
    {
       $this->setTemplate ('onestic_skyhub/payment_info.phtml');
    }
}
