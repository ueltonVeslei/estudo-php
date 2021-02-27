<?php
class Onestic_Recaptcha_Model_Checker
{
    const PARAM_RECAPTCHA = 'onestic_recaptcha';
    const PARAM_FORCE_REQUIRED = 'email';

    public function checkCaptcha($_helper)
    {
        $_hasRecaptcha = Mage::app()->getRequest()->getParam(self::PARAM_RECAPTCHA);

        if (!empty($_hasRecaptcha) && $_hasRecaptcha == 1) {
            Mage::app()->getRequest()->setPost(self::PARAM_RECAPTCHA, 'passed');
        } else {
            Mage::app()->getRequest()->setPost(self::PARAM_FORCE_REQUIRED, '');

            Mage::getSingleton('customer/session')->addError(
                $_helper->__('You must check reCaptcha validation')
            );

            Mage::app()->getFrontController()->getResponse()->setRedirect('*/*/');
        }
    }
}