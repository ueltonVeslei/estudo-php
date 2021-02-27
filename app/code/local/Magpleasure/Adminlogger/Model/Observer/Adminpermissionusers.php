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
class Magpleasure_Adminlogger_Model_Observer_Adminpermissionusers extends Magpleasure_Adminlogger_Model_Observer
{

    public function AdminPermissionUsersLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('adminpermissionusers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Adminpermissionusers::ACTION_ADMIN_PERMISSION_USERS_LOAD,
            Mage::app()->getRequest()->getParam('user_id')
        );
    }

    public function AdminPermissionUsersSave($event)
    {
        $adminUser = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('adminpermissionusers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Adminpermissionusers::ACTION_ADMIN_PERMISSION_USERS_SAVE,
            $adminUser->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($adminUser->getData(), $adminUser->getOrigData())
            );
        }
    }

    public function AdminPermissionUsersDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('adminpermissionusers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Adminpermissionusers::ACTION_ADMIN_PERMISSION_USERS_DELETE
        );
    }
}