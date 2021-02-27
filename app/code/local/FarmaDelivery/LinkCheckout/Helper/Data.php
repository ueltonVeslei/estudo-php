<?php
class FarmaDelivery_LinkCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_quote = null;

    protected function getQuote() {
        if (null === $this->_quote) {
            $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        return $this->_quote;
    }

    public function getCheckoutLink() {
        $cartId = $this->getQuote()->getId();
        if (!$urlFinal = $this->getQuote()->getDscToken()) {
            $id = 0;

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $id = $customer->getId();
            }

            $user = $cartId . ":" . $id . ":" . "D";
            $encryCart = base64_encode($user);
            $url = $encryCart . "S";
            $urlS = base64_encode($url);
            $urlC = $urlS . "C";
            $urlFinal = base64_encode($urlC);
            $this->getQuote()->setDscToken($urlFinal)->save();
        }

        return 'https://checkout.farmadelivery.com.br/?cartID=' . $cartId . '&t=' . $urlFinal;
    }
}
