<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Adminlogger
 * @copyright  Copyright (c) 2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */
class Magpleasure_Adminlogger_Model_Observer_Catalogproduct extends Magpleasure_Adminlogger_Model_Observer
{

    public function CatalogProductLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogproduct')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogproduct::ACTION_PRODUCT_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function CatalogProductSavePrepare($event)
    {
        if (Mage::registry('adminlogger_is_mass_update')){
            return $this;
        }

        /** @var $product Mage_Catalog_Model_Product */
        $product = $event->getProduct();

        if (!$product->getId()){
            Mage::register('adminlogger_is_product_create', true, true);
        }
    }

    public function CatalogProductSave($event)
    {
        if (Mage::registry('adminlogger_is_mass_update')){
            return $this;
        }

        /** @var $product Mage_Catalog_Model_Product */
        $product = $event->getProduct();
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogproduct')->getValue(),
            Mage::registry('adminlogger_is_product_create') ? Magpleasure_Adminlogger_Model_Actiongroup_Catalogproduct::ACTION_PRODUCT_CREATE : Magpleasure_Adminlogger_Model_Actiongroup_Catalogproduct::ACTION_PRODUCT_SAVE,
            $product->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($product->getData(), $product->getOrigData()),
                $product->getId()
            );
        }
    }

    /**
     * Retrieves data manipulation helper
     *
     * @return Mage_Adminhtml_Helper_Catalog_Product_Edit_Action_Attribute
     */
    protected function _getDataHelper()
    {
        return Mage::helper('adminhtml/catalog_product_edit_action_attribute');
    }

    public function CatalogProductMassAttributeUpdate($event)
    {
        Mage::register('adminlogger_is_mass_update', true, true);
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogproduct')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogproduct::ACTION_PRODUCT_MASS_ATTRUBUTE_UPDATE
        );

        if ($log){

            $productIds = $this->_getDataHelper()->getProductIds();
            $log->addDetails(array(array('attribute_code'=>'__products__', 'from'=> null, 'to'=> serialize($productIds))));

            $keys = array('attributes', 'product', 'inventory');
            $post = Mage::app()->getRequest()->getPost();

            $details = array();

            foreach ($post as $key=>$value){
                if (is_array($value)){
                    foreach ($value as $attributeCode=>$dataValue){
                        $details[] = array('attribute_code'=>$attributeCode, 'from'=> null, 'to'=> $dataValue);
                    }
                }
            }

            $log->addDetails(
                $details
            );
        }
    }

    public function CatalogProductDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogproduct')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogproduct::ACTION_PRODUCT_DELETE
        );
    }


}