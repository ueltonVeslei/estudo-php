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
class Magpleasure_Adminlogger_Model_Observer_Newsletterqueue extends Magpleasure_Adminlogger_Model_Observer
{

    public function NewsletterQueueLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('newsletterqueue')->getValue(),
            (Mage::app()->getRequest()->getParam('template_id') ? Magpleasure_Adminlogger_Model_Actiongroup_Newsletterqueue::ACTION_NEWSLETTER_QUEUE_CREATE : Magpleasure_Adminlogger_Model_Actiongroup_Newsletterqueue::ACTION_NEWSLETTER_QUEUE_LOAD),
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function NewsletterQueueSave($event)
    {
        $queue = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('newsletterqueue')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Newsletterqueue::ACTION_NEWSLETTER_QUEUE_SAVE,
            $queue->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($queue->getData(), $queue->getOrigData())
            );
        }
    }

}