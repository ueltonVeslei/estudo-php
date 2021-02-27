<?php
class Onestic_Skyhub_Model_Observer
{
    public function updateOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        if($order->getMarketplaceId()) {
            Mage::getModel('onestic_skyhub/checker')->checkOrder($order->getId());
        }
    }
    
    public function addProductsMassaction($observer) {
        $block = $observer->getEvent()->getBlock();
        $block->getMassactionBlock()->addItem('send_skyhub', array(
            'label'=> Mage::helper('onestic_skyhub')->__('Enviar para Skyhub'),
            'url'  => Mage::getUrl('skyhub/admin/send'),
        ));
    }
    
    public function addOrdersMassaction($observer) {
        if (!($observer->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Grid)) {
            return $this;
        }
        $block = $observer->getEvent()->getBlock();
        $block->getMassactionBlock()->addItem('send_skyhub', array(
            'label'=> Mage::helper('onestic_skyhub')->__('Sincronizar Skyhub'),
            'url'  => Mage::getUrl('skyhub/admin/sync'),
        ));
    }
    
    public function updateProductByModel(Varien_Event_Observer $observer)
    {
        $object = $observer->getObject();

        if (!($object instanceof Mage_Catalog_Model_Product ||
            $object instanceof Mage_CatalogInventory_Model_Stock_Item ||
            $object instanceof Mage_CatalogInventory_Model_Stock_Status))
            return false;

        $productId = ($object instanceof Mage_Catalog_Model_Product) ? $object->getId() : $object->getProductId();

        Mage::getModel('onestic_skyhub/products_updater')->queue($productId);
    }
       
}
