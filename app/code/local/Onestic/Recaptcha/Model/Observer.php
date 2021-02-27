<?php
/**
 * Onestic_Recaptcha
 *
 * @category   Onestic
 * @package    Onestic_Recaptcha
 * @copyright  Copyright (c) 2018 Onestic. (http://www.onestic.com/)
 */

class Onestic_Recaptcha_Model_Observer {

    const HELPER_RECAPTCHA = 'onestic_recaptcha/data';
    const MODEL_CHECKER = 'onestic_recaptcha/checker';

    private $helperRecaptcha = null;
    private $modelChecker = null;

    public function __construct() {
        $this->helperRecaptcha = Mage::helper(self::HELPER_RECAPTCHA);
        $this->modelChecker = Mage::getModel(self::MODEL_CHECKER);
    }

    public function checkCaptchaContact($observer) {
        if ($this->helperRecaptcha->getContactPageEnabled() || $this->helperRecaptcha->getAllPagesEnabled()) {
            $this->modelChecker->checkCaptcha($this->helperRecaptcha);
        }
    }

    public function checkCaptchaForgotPass($observer) {
        if ($this->helperRecaptcha->getForgotPasswordEnabled() || $this->helperRecaptcha->getAllPagesEnabled()) {
            $this->modelChecker->checkCaptcha($this->helperRecaptcha);
        }
    }

    public function checkCaptchaCreateAccount($observer) {
        if ($this->helperRecaptcha->getCreateAccountEnabled() || $this->helperRecaptcha->getAllPagesEnabled()) {
            $this->modelChecker->checkCaptcha($this->helperRecaptcha);
        }
    }

}