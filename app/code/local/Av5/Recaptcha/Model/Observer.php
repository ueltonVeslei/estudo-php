<?php
/**
 * Av5_Recaptcha
 *
 * @category   Av5
 * @package    Av5_Recaptcha
 * @copyright  Copyright (c) 2018 Av5. (http://www.av5.com.br/)
 */

class Av5_Recaptcha_Model_Observer {

    const HELPER_RECAPTCHA = 'av5_recaptcha/data';
    const MODEL_CHECKER = 'av5_recaptcha/checker';

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

    public function checkCaptchaReview($observer) {
        if ($this->helperRecaptcha->getReviewEnabled() || $this->helperRecaptcha->getAllPagesEnabled()) {
            $this->modelChecker->checkCaptcha($this->helperRecaptcha);
        }
    }

}
