<?php
class Onestic_Smartpbm_Model_Source_Funcional_Types
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'PreAutorizacao', 'label'=>Mage::helper('adminhtml')->__('PreAutorizacao')),
            array('value'=>'CompraFarmaco', 'label'=>Mage::helper('adminhtml')->__('CompraFarmaco')),
            array('value'=>'PreAutorizacaoCentral', 'label'=>Mage::helper('adminhtml')->__('PreAutorizacaoCentral')),
            array('value'=>'PreAutorizacaoOnline', 'label'=>Mage::helper('adminhtml')->__('PreAutorizacaoOnline')),
            array('value'=>'CompraFarmacoCentral', 'label'=>Mage::helper('adminhtml')->__('CompraFarmacoCentral')),
            array('value'=>'CompraFarmacoOnline', 'label'=>Mage::helper('adminhtml')->__('CompraFarmacoOnline')),
        );
    }

}