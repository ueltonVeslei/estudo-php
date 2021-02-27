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
class Magpleasure_Adminlogger_Model_Observer_Systemdesign extends Magpleasure_Adminlogger_Model_Observer
{

    public function SystemDesignLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('systemdesign')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Systemdesign::ACTION_DESIGN_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function SystemDesignSave($event)
    {
        $design = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('systemdesign')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Systemdesign::ACTION_DESIGN_SAVE,
            $design->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($design->getData(), $design->getOrigData())
            );
        }
    }

    public function SystemDesignDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('systemdesign')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Systemdesign::ACTION_DESIGN_DELETE
        );
    }
}