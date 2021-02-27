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
class Magpleasure_Adminlogger_Model_Observer_Catalogcategory extends Magpleasure_Adminlogger_Model_Observer
{

    public function CatalogCategoryLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogcategory')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogcategory::ACTION_CATEGORY_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function CatalogCategorySave($event)
    {
        $category = $event->getCategory();
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogcategory')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogcategory::ACTION_CATEGORY_SAVE,
            $category->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($category->getData(), $category->getOrigData())
            );
        }
    }

    public function CatalogCategoryDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogcategory')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogcategory::ACTION_CATEGORY_DELETE
        );
    }
}