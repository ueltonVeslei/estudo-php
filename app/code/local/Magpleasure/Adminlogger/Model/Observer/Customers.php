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
class Magpleasure_Adminlogger_Model_Observer_Customers extends Magpleasure_Adminlogger_Model_Observer
{
    public function CustomersLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('customers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Customers::ACTION_CUSTOMERS_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function CustomersSave($event)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $event->getCustomer();
        $log = $this->createLogRecord(
            $this->getActionGroup('customers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Customers::ACTION_CUSTOMERS_SAVE,
            $customer->getId()
        );
        if ($log){
            $customerOrigData = $customer->getOrigData();
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
            $customerOrigData['is_subscribed'] = $subscriber->isSubscribed();
            $log->addDetails(
                $this->_helper()->getCompare()->diff($customer->getData(), $customerOrigData)
            );
        }
    }

    public function CustomersDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('customers')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Customers::ACTION_CUSTOMERS_DELETE
        );
    }
}