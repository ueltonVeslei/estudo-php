<?php

class Edrone_Base_NewsletterController extends Mage_Core_Controller_Front_Action {

    public function updateAction() {
        header("Cache-Control: no-cache, max-age=0");
        $helper = Mage::helper('edrone');
        $configHelper = Mage::helper('edrone/config');

        if (!$configHelper->isNewsletterSyncEnabled()) {
            $this->getResponse()->setBody('1');
            return;
        }

        $signature = $this->getRequest()->getParam('signature');
        $email = $this->getRequest()->getParam('email');
        $subscriberStatus = $this->getRequest()->getParam('subscriber_status');
        $eventDate = $this->getRequest()->getParam('event_date');
        $eventId = $this->getRequest()->getParam('event_id');

        if ($email && $signature && $eventDate && $eventId && $helper->validateToken($email, $signature, $subscriberStatus, $eventDate, $eventId)) {
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            if ($subscriberStatus == 0) {
                $subscriber->unsubscribe();
                $this->getResponse()->setBody('us');
            } elseif ($subscriberStatus == 1) {
                $subscriber->subscribe($email);
                $subscriber->setStatus(1)->save();
                $this->getResponse()->setBody('ss');
            }
        } else {
            $this->getResponse()->setBody('invalidQuery');
        }
        return;
    }
}
