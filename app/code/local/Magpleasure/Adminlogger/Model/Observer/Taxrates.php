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

class Magpleasure_Adminlogger_Model_Observer_Taxrates extends Magpleasure_Adminlogger_Model_Observer
{

    public function TaxRatesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('taxrates')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Taxrates::ACTION_TAX_RATES_LOAD,
            Mage::app()->getRequest()->getParam('rate')
        );
    }

    public function TaxRatesSave($event)
    {
        $rate = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('taxrates')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Taxrates::ACTION_TAX_RATES_SAVE,
            $rate->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($rate->getData(), $rate->getOrigData())
            );
        }
    }

    public function TaxRatesDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('taxrates')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Taxrates::ACTION_TAX_RATES_DELETE
        );
    }
}