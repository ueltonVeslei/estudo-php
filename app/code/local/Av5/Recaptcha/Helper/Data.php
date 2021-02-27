<?php
/**
 * Av5_Recaptcha
 *
 * @category   Av5
 * @package    Av5_Recaptcha
 * @copyright  Copyright (c) 2018 Av5. (http://www.av5.com.br/)
 */

class Av5_Recaptcha_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getAllPagesEnabled() {
        return  Mage::getStoreConfig('av5_recaptcha/config/allpages',Mage::app()->getStore()->getStoreId());
    }

    public function getContactPageEnabled() {
        return  Mage::getStoreConfig('av5_recaptcha/config/contactpage',Mage::app()->getStore()->getStoreId());
    }

    public function getCreateAccountEnabled() {
        return  Mage::getStoreConfig('av5_recaptcha/config/createaccount',Mage::app()->getStore()->getStoreId());
    }

    public function getForgotPasswordEnabled() {
        return  Mage::getStoreConfig('av5_recaptcha/config/forgotpassword',Mage::app()->getStore()->getStoreId());
    }

    public function getReviewEnabled() {
        return  Mage::getStoreConfig('av5_recaptcha/config/review',Mage::app()->getStore()->getStoreId());
    }
}
