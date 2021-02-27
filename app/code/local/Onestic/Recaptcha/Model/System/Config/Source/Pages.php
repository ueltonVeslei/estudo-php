<?php
/**
 * Onestic_Recaptcha
 *
 * @category   Onestic
 * @package    Onestic_Recaptcha
 * @copyright  Copyright (c) 2018 Onestic. (http://www.onestic.com/)
 */

class Onestic_Recaptcha_Model_System_Config_Source_Pages {

    public function toOptionArray() {
        return array(
            array('value' => 'allPages',        'label' => Mage::helper('onestic_recaptcha')->__('All Pages')),
            array('value' => 'createAccount',   'label' => Mage::helper('onestic_recaptcha')->__('Create Account')),
            array('value' => 'forgotPassword',  'label' => Mage::helper('onestic_recaptcha')->__('Forgot Password')),
        );
    }

    public function toArray() {
        return array(
            'allPages' => Mage::helper('onestic_recaptcha')->__('All Pages'),
            'createAccount'  => Mage::helper('onestic_recaptcha')->__('Create Account'),
            'forgotPassword'  => Mage::helper('onestic_recaptcha')->__('Forgot Password'),
        );
    }
}