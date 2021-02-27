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
class Magpleasure_Adminlogger_Model_Observer_Webservicesapiusers extends Magpleasure_Adminlogger_Model_Observer
{

    public function ApiUsersLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('webservicesapiusers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Webservicesapiusers::ACTION_API_USER_LOAD,
            Mage::app()->getRequest()->getParam('user_id')
        );
    }

    public function ApiUsersSave($event)
    {
        $apiUser = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('webservicesapiusers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Webservicesapiusers::ACTION_API_USER_SAVE,
            $apiUser->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($apiUser->getData(), $apiUser->getOrigData())
            );
        }
    }

    public function ApiUsersDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('webservicesapiusers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Webservicesapiusers::ACTION_API_USER_DELETE
        );
    }
}