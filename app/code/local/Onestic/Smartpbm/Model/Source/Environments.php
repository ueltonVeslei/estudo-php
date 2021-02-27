<?php
class Onestic_Smartpbm_Model_Source_Environments {

    public function toOptionArray() {
        return array(
            array('value'=>'D', 'label'=>Mage::helper('adminhtml')->__('Desenvolvimento')),
            array('value'=>'P', 'label'=>Mage::helper('adminhtml')->__('Produção')),
        );
    }

}