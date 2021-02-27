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
class Magpleasure_Adminlogger_Model_Observer_Salesshipments extends Magpleasure_Adminlogger_Model_Observer
{

    public function SalesShipmentsLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salesshipments')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesshipments::ACTION_SALES_SHIPMENTS_LOAD,
            Mage::app()->getRequest()->getParam('shipment_id')
        );
    }

    public function SalesShipmentsEmail($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salesshipments')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesshipments::ACTION_SALES_SHIPMENTS_SEND_TRACKING_INFO,
            Mage::app()->getRequest()->getParam('shipment_id')
        );
    }

    public function SalesShipmentsPrint($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('salesshipments')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Salesshipments::ACTION_SALES_SHIPMENTS_PRINT,
            Mage::app()->getRequest()->getParam('shipment_id')
        );
    }

    public function SalesShipmentsSave($event)
    {
        $shipment = $event->getShipment();
        if ($shipment) {
            $log = $this->createLogRecord(
                $this->getActionGroup('salesshipments')->getValue(),
                Magpleasure_Adminlogger_Model_Actiongroup_Salesshipments::ACTION_SALES_SHIPMENTS_SAVE,
                $shipment->getId()
            );

            if ($log){
                $log->addDetails(
                    $this->_helper()->getCompare()->diff($shipment->getData(), $shipment->getOrigData())
                );
            }
        }
    }


}
