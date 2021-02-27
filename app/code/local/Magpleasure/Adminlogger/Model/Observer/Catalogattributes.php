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
class Magpleasure_Adminlogger_Model_Observer_Catalogattributes extends Magpleasure_Adminlogger_Model_Observer
{

    public function CatalogAttributesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogattributes')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogattributes::ACTION_CATALOG_ATTRIBUTES_LOAD,
            Mage::app()->getRequest()->getParam('attribute_id')
        );
    }

    public function CatalogAttributesSave($event)
    {
        $Attribute = $event->getAttribute();
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogattributes')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogattributes::ACTION_CATALOG_ATTRIBUTES_SAVE,
            $Attribute->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($Attribute->getData(), $Attribute->getOrigData())
            );
        }
    }

    public function CatalogAttributesDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogattributes')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogattributes::ACTION_CATALOG_ATTRIBUTES_DELETE
        );
    }
}