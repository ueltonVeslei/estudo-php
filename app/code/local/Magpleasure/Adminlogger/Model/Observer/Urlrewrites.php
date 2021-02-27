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
class Magpleasure_Adminlogger_Model_Observer_Urlrewrites extends Magpleasure_Adminlogger_Model_Observer
{

    public function UrlRewriteLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('urlrewrites')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Urlrewrites::ACTION_URL_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function UrlRewriteSave($event)
    {
        $rewrite = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('urlrewrites')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Urlrewrites::ACTION_URL_SAVE,
            $rewrite->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($rewrite->getData(), $rewrite->getOrigData())
            );
        }
    }


    public function UrlRewriteDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('urlrewrites')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Urlrewrites::ACTION_URL_DELETE
        );
    }
}