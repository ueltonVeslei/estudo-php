<?php
class Onestic_Edrone_Model_Observer
{

    public function sendWishlist(Varien_Event_Observer $observer)
    {

        if (Mage::helper('onestic_edrone')->getConfig('active')) {

            $event = $observer->getEvent();
            $_item = $event->getItem();

            if ((int)$_item->getData('qty') > 0 && (int)$_item->getOrigData('qty') == 0) {
                Mage::getModel('onestic_edrone/api')->send($_item->getProductId());
            }

        }
        
    }

}