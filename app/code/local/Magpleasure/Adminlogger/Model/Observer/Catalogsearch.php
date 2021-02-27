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

class Magpleasure_Adminlogger_Model_Observer_Catalogsearch extends Magpleasure_Adminlogger_Model_Observer
{
    public function CatalogSearchLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogsearch')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogsearch::ACTION_CATALOG_SEARCH_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function CatalogSearchSave($event)
    {
        $search = $event->getCatalogsearchQuery();
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogsearch')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogsearch::ACTION_CATALOG_SEARCH_SAVE,
            $search->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($search->getData(), $search->getOrigData())
            );
        }
    }

    public function CatalogSearchDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogsearch')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogsearch::ACTION_CATALOG_SEARCH_DELETE
        );
    }
}
