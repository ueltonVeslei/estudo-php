<?php


class Saffira_Koin_Model_Source_Payment
{
    public function toOptionArray()
    {
        return array(
            array('value' => Saffira_Koin_Model_System_Config_Source_Order_Status_Koin::STATE_KOIN_ACCREDITED, 'label'=>Mage::helper('adminhtml')->__('Aprovado Koin')),
            array('value' => Mage_Sales_Model_Order::STATE_PROCESSING, 'label'=>Mage::helper('adminhtml')->__('Processando')),
            array('value' => Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, 'label'=>Mage::helper('adminhtml')->__('Em Análise'))

        );
    }
}