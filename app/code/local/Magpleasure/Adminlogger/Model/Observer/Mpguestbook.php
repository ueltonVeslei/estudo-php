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
class Magpleasure_Adminlogger_Model_Observer_Mpguestbook extends Magpleasure_Adminlogger_Model_Observer
{
    public function MpGuestbookMessageLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpguestbook')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpguestbook::ACTION_MESSAGE_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function MpGuestbookMessageSave($event)
    {
        $message = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('mpguestbook')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpguestbook::ACTION_MESSAGE_SAVE,
            $message->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($message->getData(), $message->getOrigData())
            );
        }
    }

    public function MpGuestbookMessageDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpguestbook')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpguestbook::ACTION_MESSAGE_DELETE
        );
    }
}
