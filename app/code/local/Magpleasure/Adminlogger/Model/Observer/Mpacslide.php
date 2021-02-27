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
class Magpleasure_Adminlogger_Model_Observer_Mpacslide extends Magpleasure_Adminlogger_Model_Observer
{
    public function MpAcSlideLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpacslide')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpacslide::ACTION_SLIDE_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function MpAcSlideSave($event)
    {
        $slide = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('mpacslide')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpacslide::ACTION_SLIDE_SAVE,
            $slide->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($slide->getData(), $slide->getOrigData())
            );
        }

    }

    public function MpAcSlideDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpacslide')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpacslide::ACTION_SLIDE_DELETE
        );
    }
}
