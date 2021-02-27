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
class Magpleasure_Adminlogger_Model_Observer_Mpacslider extends Magpleasure_Adminlogger_Model_Observer
{
    public function MpAcSliderLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpacslider')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpacslider::ACTION_SLIDER_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function MpAcSliderAddSlide($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpacslider')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpacslider::ACTION_SLIDER_ADDSLIDE
        );
    }

    public function MpAcSliderSave($event)
    {
        $slider = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('mpacslider')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpacslider::ACTION_SLIDER_SAVE,
            $slider->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($slider->getData(), $slider->getOrigData())
            );
        }
    }

    public function MpAcSliderDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpacslider')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpacslider::ACTION_SLIDER_DELETE
        );
    }


}
