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
class Magpleasure_Adminlogger_Model_Observer_Awhduticket extends Magpleasure_Adminlogger_Model_Observer
{
    public function AwHduTicketLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('awhduticket')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Awhduticket::ACTION_TICKET_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function AwHduTicketSave($event)
    {
        if (Mage::registry('adminlogger_aw_already_saved')){
            return $this;
        }

        $ticket = $event->getObject();
        $post = Mage::app()->getRequest()->getPost();

        $log = $this->createLogRecord(
            $this->getActionGroup('awhduticket')->getValue(),
            (isset($post['email']) && $post['email']) ? Magpleasure_Adminlogger_Model_Actiongroup_Awhduticket::ACTION_TICKET_SAVE_AND_EMAIL : Magpleasure_Adminlogger_Model_Actiongroup_Awhduticket::ACTION_TICKET_SAVE,
            $ticket->getId()
        );

        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($ticket->getData(), $ticket->getOrigData())
            );
        }

        Mage::register('adminlogger_aw_already_saved', true, true);
    }

    public function AwHduTicketDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('awhduticket')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Awhduticket::ACTION_TICKET_DELETE
        );
    }
}
