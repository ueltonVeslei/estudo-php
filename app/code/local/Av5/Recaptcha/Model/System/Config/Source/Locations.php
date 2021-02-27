<?php
/**
 * Av5_Recaptcha
 *
 * @category   Av5
 * @package    Av5_Recaptcha
 * @copyright  Copyright (c) 2018 Av5. (http://www.av5.com.br/)
 */

class Av5_Recaptcha_Model_System_Config_Source_Locations {

    public function toOptionArray() {
        return array(
            array('value' => 'before',  'label' => Mage::helper('av5_recaptcha')->__('Before')),
            array('value' => 'after',  'label' => Mage::helper('av5_recaptcha')->__('After')),
            array('value' => 'append',  'label' => Mage::helper('av5_recaptcha')->__('Append')),
            array('value' => 'prepend',  'label' => Mage::helper('av5_recaptcha')->__('Prepend')),
        );
    }

    public function toArray() {
        return array(
            'before' => Mage::helper('av5_recaptcha')->__('Before'),
            'after'  => Mage::helper('av5_recaptcha')->__('After'),
            'append'  => Mage::helper('av5_recaptcha')->__('Append'),
            'prepend'  => Mage::helper('av5_recaptcha')->__('Prepend'),
        );
    }
}
