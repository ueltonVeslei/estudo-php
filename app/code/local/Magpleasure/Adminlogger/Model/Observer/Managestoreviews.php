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
class Magpleasure_Adminlogger_Model_Observer_Managestoreviews extends Magpleasure_Adminlogger_Model_Observer
{

    public function ManageStoreViewsLoad($event)
    {
        if (Mage::registry('store_type')){
            return $this;
        }

        $this->createLogRecord(
            $this->getActionGroup('managestoreviews')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Managestoreviews::ACTION_MANAGE_STORE_VIEWS_LOAD,
            Mage::app()->getRequest()->getParam('store_id')
        );
    }

    public function ManageStoreViewsSave($event)
    {
        $storeView = $event->getStore();
        $log = $this->createLogRecord(
            $this->getActionGroup('managestoreviews')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Managestoreviews::ACTION_MANAGE_STORE_VIEWS_SAVE,
            $storeView->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($storeView->getData(), $storeView->getOrigData())
            );
        }
    }

    public function ManageStoreViewsDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('managestoreviews')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Managestoreviews::ACTION_MANAGE_STORE_VIEWS_DELETE
        );
    }
}