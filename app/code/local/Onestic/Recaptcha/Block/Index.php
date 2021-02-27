<?php
/**
 * Onestic_Recaptcha
 *
 * @category   Onestic
 * @package    Onestic_Recaptcha
 * @copyright  Copyright (c) 2018 Onestic. (http://www.onestic.com/)
 */

class Onestic_Recaptcha_Block_Index extends Mage_Core_Block_Template {

    protected function getConfig($group, $key) {
        $_storeId = Mage::app()->getStore()->getStoreId();
        return Mage::getStoreConfig(
            sprintf("onestic_recaptcha/%s/%s", $group, $key),
            $_storeId
        );
    }

    public function getSiteKey() {
        return $this->getConfig("api", "site_key");
    }

    public function getLocationId() {
        return $this->getConfig("general", "location_id");
    }

    public function getLocationType() {
        return $this->getConfig("general", "location_type");
    }

    public function getCaptchaId() {
        return $this->getConfig("general", "captcha_id");
    }

    public function getCaptchaCss() {
        return preg_replace( "/\r|\n/", " ", $this->getConfig("general", "captcha_css"));
    }

    public function getValidationCss() {
        return preg_replace( "/\r|\n/", " ", $this->getConfig("general", "validation_css"));
    }

    public function getExtraStyle() {
        return $this->getConfig("general", "extra_css");
    }
}