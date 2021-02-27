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
class Magpleasure_Adminlogger_Model_Observer_Transactionalemails extends Magpleasure_Adminlogger_Model_Observer
{

    public function TransactionalEmailsLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('transactionalemails')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Transactionalemails::ACTION_TRANSACTIONAL_EMAILS_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function TransactionalEmailsSave($event)
    {
        $email = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('transactionalemails')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Transactionalemails::ACTION_TRANSACTIONAL_EMAILS_SAVE,
            $email->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($email->getData(), $email->getOrigData())
            );
        }
    }

    public function TransactionalEmailsDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('transactionalemails')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Transactionalemails::ACTION_TRANSACTIONAL_EMAILS_DELETE
        );
    }
}