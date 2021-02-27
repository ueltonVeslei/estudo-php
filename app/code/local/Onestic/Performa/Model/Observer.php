<?php
class Onestic_Performa_Model_Observer
{
    
    public function updateProductByModel(Varien_Event_Observer $observer)
    {
        $object = $observer->getObject();

        if (!($object instanceof Mage_Catalog_Model_Product ||
            $object instanceof Mage_CatalogInventory_Model_Stock_Item ||
            $object instanceof Mage_CatalogInventory_Model_Stock_Status))
            return false;

        $productId = ($object instanceof Mage_Catalog_Model_Product) ? $object->getId() : $object->getProductId();

        Mage::getModel('onestic_performa/queue_updater')->queue($productId);
    }
       
}
