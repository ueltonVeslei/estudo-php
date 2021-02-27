<?php
/**
 * Onestic_Recaptcha
 *
 * @category   Onestic
 * @package    Onestic_Recaptcha
 * @copyright  Copyright (c) 2018 Onestic. (http://www.onestic.com/)
 */

class Onestic_Recaptcha_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getAllPagesEnabled() {
        return  Mage::getStoreConfig('onestic_recaptcha/config/allpages',Mage::app()->getStore()->getStoreId());
    }

    public function getContactPageEnabled() {
        return  Mage::getStoreConfig('onestic_recaptcha/config/contactpage',Mage::app()->getStore()->getStoreId());
    }

    public function getCreateAccountEnabled() {
        return  Mage::getStoreConfig('onestic_recaptcha/config/createaccount',Mage::app()->getStore()->getStoreId());
    }

    public function getForgotPasswordEnabled() {
        return  Mage::getStoreConfig('onestic_recaptcha/config/forgotpassword',Mage::app()->getStore()->getStoreId());
    }
}