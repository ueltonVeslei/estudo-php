<?php
class Av5_Exporter_Model_Observer
{
    public function addProductsMassaction($observer) {
        $block = $observer->getEvent()->getBlock();
        $block->getMassactionBlock()->addItem('av5_exporter', array(
            'label'=> Mage::helper('av5_exporter')->__('Exportar Produtos'),
            'url'  => Mage::getUrl('exportador/index/index'),
        ));
    }

}
