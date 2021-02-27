<?php
/**
 * Onestic_Recaptcha
 *
 * @category   Onestic
 * @package    Onestic_Recaptcha
 * @copyright  Copyright (c) 2018 Onestic. (http://www.onestic.com/)
 */

class Onestic_Recaptcha_Model_System_Config_Source_Locations {

    public function toOptionArray() {
        return array(
            array('value' => 'before',  'label' => Mage::helper('onestic_recaptcha')->__('Before')),
            array('value' => 'after',  'label' => Mage::helper('onestic_recaptcha')->__('After')),
            array('value' => 'append',  'label' => Mage::helper('onestic_recaptcha')->__('Append')),
            array('value' => 'prepend',  'label' => Mage::helper('onestic_recaptcha')->__('Prepend')),
        );
    }

    public function toArray() {
        return array(
            'before' => Mage::helper('onestic_recaptcha')->__('Before'),
            'after'  => Mage::helper('onestic_recaptcha')->__('After'),
            'append'  => Mage::helper('onestic_recaptcha')->__('Append'),
            'prepend'  => Mage::helper('onestic_recaptcha')->__('Prepend'),
        );
    }
}