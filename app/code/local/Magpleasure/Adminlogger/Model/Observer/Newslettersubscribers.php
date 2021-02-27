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

class Magpleasure_Adminlogger_Model_Observer_Newslettersubscribers extends Magpleasure_Adminlogger_Model_Observer
{

    public function SubscribersUnsubscribed($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('newslettersubscribers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Newslettersubscribers::ACTION_NEWSLETTER_SUBSCRIBERS_UNSUBSCRIBED
        );

        $subscriberIds = Mage::app()->getRequest()->getPost('subscriber');
        $subscribers = array();
        foreach ($subscriberIds as $subscriberId){
            $subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
            $subscribers[] = $subscriber->getEmail();
        }

        if (!is_array($subscribers)) {
            $subscribers = array($subscribers);
        }
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($subscribers));
        }
    }

    public function SubscribersDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('newslettersubscribers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Newslettersubscribers::ACTION_NEWSLETTER_SUBSCRIBERS_DELETE
        );
    }
}