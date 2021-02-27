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
class Magpleasure_Adminlogger_Model_Observer_Cmspages extends Magpleasure_Adminlogger_Model_Observer
{
    public function CmsPagesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('cmspages')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cmspages::ACTION_PAGES_LOAD,
            Mage::app()->getRequest()->getParam('page_id')
        );
    }

    public function CmsPagesSave($event)
    {
        $pages = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('cmspages')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cmspages::ACTION_PAGES_SAVE,
            $pages->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($pages->getData(), $pages->getOrigData())
            );
        }

    }

    public function CmsPagesDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('cmspages')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cmspages::ACTION_PAGES_DELETE
        );
    }
}
