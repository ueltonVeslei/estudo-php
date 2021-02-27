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
class Magpleasure_Adminlogger_Model_Observer_Managewebsites extends Magpleasure_Adminlogger_Model_Observer
{

    public function ManageWebsitesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('managewebsites')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Managewebsites::ACTION_MANAGE_WEBSITES_LOAD,
            Mage::app()->getRequest()->getParam('website_id')
        );
    }

    public function ManageWebsitesSave($event)
    {
        $website = $event->getWebsite();
        $log = $this->createLogRecord(
            $this->getActionGroup('managewebsites')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Managewebsites::ACTION_MANAGE_WEBSITES_SAVE,
            $website->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($website->getData(), $website->getOrigData())
            );
        }
    }

    public function ManageWebsitesDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('managewebsites')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Managewebsites::ACTION_MANAGE_WEBSITES_DELETE
        );
    }
}