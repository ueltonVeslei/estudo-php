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
class Magpleasure_Adminlogger_Model_Observer_Webservicesapiroles extends Magpleasure_Adminlogger_Model_Observer
{

    public function ApiRolesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('webservicesapiroles')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Webservicesapiroles::ACTION_API_ROLES_LOAD,
            Mage::app()->getRequest()->getParam('rid')
        );
    }

    public function ApiRolesSave($event)
    {
        $apiRole = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('webservicesapiroles')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Webservicesapiroles::ACTION_API_ROLES_SAVE,
            $apiRole->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($apiRole->getData(), $apiRole->getOrigData())
            );
        }
    }

    public function ApiRolesDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('webservicesapiroles')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Webservicesapiroles::ACTION_API_ROLES_DELETE
        );
    }
}