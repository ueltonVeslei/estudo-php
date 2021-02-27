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

class Magpleasure_Adminlogger_Model_Observer_Salescreditmemos extends Magpleasure_Adminlogger_Model_Observer
{

    public function SalesCreditmemosLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salescreditmemos')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salescreditmemos::ACTION_SALES_CREDITMEMOS_LOAD,
            Mage::app()->getRequest()->getParam('creditmemo_id')
        );
    }

    public function SalesCreditmemosEmail($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salescreditmemos')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salescreditmemos::ACTION_SALES_CREDITMEMOS_EMAIL,
            Mage::app()->getRequest()->getParam('creditmemo_id')
        );
    }

    public function SalesCreditmemosPrint($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salescreditmemos')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salescreditmemos::ACTION_SALES_CREDITMEMOS_PRINT,
            Mage::app()->getRequest()->getParam('creditmemo_id')
        );
    }

    public function SalesCreditmemosSave($event)
    {
        $creditmemo = $event->getCreditmemo();
        if ($creditmemo) {
            $log = $this->createLogRecord(
                $this->getActionGroup('salescreditmemos')->getValue(),
                Magpleasure_Adminlogger_Model_Actiongroup_Salescreditmemos::ACTION_SALES_CREDITMEMOS_CREATE,
                $creditmemo->getId()
            );

            if ($log){
                $log->addDetails(
                    $this->_helper()->getCompare()->diff($creditmemo->getData(), $creditmemo->getOrigData())
                );
            }
        }
    }

}
