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
class Magpleasure_Adminlogger_Model_Observer_Adminpermissionroles extends Magpleasure_Adminlogger_Model_Observer
{

    public function AdminPermissionRolesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('adminpermissionroles')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Adminpermissionroles::ACTION_ADMIN_PERMISSION_ROLES_LOAD,
            Mage::app()->getRequest()->getParam('rid')
        );
    }

    public function AdminPermissionRolesSave($event)
    {
        $adminRole = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('adminpermissionroles')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Adminpermissionroles::ACTION_ADMIN_PERMISSION_ROLES_SAVE,
            $adminRole->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($adminRole->getData(), $adminRole->getOrigData())
            );
        }
    }

    public function AdminPermissionRolesDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('adminpermissionroles')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Adminpermissionroles::ACTION_ADMIN_PERMISSION_ROLES_DELETE
        );
    }
}