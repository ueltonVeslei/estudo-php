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
class Magpleasure_Adminlogger_Model_Observer_Catalogattributesets extends Magpleasure_Adminlogger_Model_Observer
{

    public function CatalogAttributeSetsLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogattributesets')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogattributesets::ACTION_CATALOG_ATTRIBUTE_SETS_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function CatalogAttributeSetsSave($event)
    {
        $set = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogattributesets')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogattributesets::ACTION_CATALOG_ATTRIBUTE_SETS_SAVE,
            $set->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($set->getData(), $set->getOrigData())
            );
        }
    }

    public function CatalogAttributeSetsDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogattributesets')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogattributesets::ACTION_CATALOG_ATTRIBUTE_SETS_DELETE
        );
    }
}