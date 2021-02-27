<?php
/**
 * Av5_Recaptcha
 *
 * @category   Av5
 * @package    Av5_Recaptcha
 * @copyright  Copyright (c) 2018 Av5. (http://www.av5.com.br/)
 */

class Av5_Recaptcha_Model_System_Config_Source_Pages {

    public function toOptionArray() {
        return array(
            array('value' => 'allPages',        'label' => Mage::helper('av5_recaptcha')->__('All Pages')),
            array('value' => 'createAccount',   'label' => Mage::helper('av5_recaptcha')->__('Create Account')),
            array('value' => 'forgotPassword',  'label' => Mage::helper('av5_recaptcha')->__('Forgot Password')),
            array('value' => 'review',          'label' => Mage::helper('av5_recaptcha')->__('Review')),
        );
    }

    public function toArray() {
        return array(
            'allPages' => Mage::helper('av5_recaptcha')->__('All Pages'),
            'createAccount'  => Mage::helper('av5_recaptcha')->__('Create Account'),
            'forgotPassword'  => Mage::helper('av5_recaptcha')->__('Forgot Password'),
            'review'  => Mage::helper('av5_recaptcha')->__('Review'),
        );
    }
}
