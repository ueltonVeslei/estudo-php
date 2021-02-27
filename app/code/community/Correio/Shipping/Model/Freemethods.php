<?php
/**
 * Entrega via EntregaExpressa
 *
 * @category   EntregaExpressa
 * @package    EntregaExpressa_Shipping
 * @author     Igor Pfeilsticker <igorsop@gmail.com>
 */
class Correio_Shipping_Model_FreeMethods
{

    public function toOptionArray()
    {
        return array(
            array('value'=>41106, 'label'=>Mage::helper('adminhtml')->__('PAC')),
            array('value'=>40010, 'label'=>Mage::helper('adminhtml')->__('Sedex')),
            array('value'=>40215, 'label'=>Mage::helper('adminhtml')->__('Sedex 10')),
            array('value'=>40290, 'label'=>Mage::helper('adminhtml')->__('Sedex HOJE')),
            array('value'=>81019, 'label'=>Mage::helper('adminhtml')->__('E-Sedex'))
        );
    }

}