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
class Magpleasure_Adminlogger_Model_Observer_Customergroups extends Magpleasure_Adminlogger_Model_Observer
{


    public function CustomerGroupsLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('customergroups')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Customergroups::ACTION_CUSTOMERGROUPS_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function CustomerGroupsSave($event)
    {
        $customerGroup = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('customergroups')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Customergroups::ACTION_CUSTOMERGROUPS_SAVE,
            $customerGroup->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($customerGroup->getData(), $customerGroup->getOrigData())
            );
        }
    }

    public function CustomerGroupsDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('customergroups')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Customergroups::ACTION_CUSTOMERGROUPS_DELETE
        );
    }

}