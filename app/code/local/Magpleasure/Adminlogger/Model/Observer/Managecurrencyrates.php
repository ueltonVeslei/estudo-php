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
class Magpleasure_Adminlogger_Model_Observer_Managecurrencyrates extends Magpleasure_Adminlogger_Model_Observer
{

    public function CurrencyRatesView($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('managecurrencyrates')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Managecurrencyrates::ACTION_CURRENCY_RATES_VIEW
        );
    }

    public function CurrencyRatesSave($event)
    {
        $Rates = $event->getObject();

        $log = $this->createLogRecord(
            $this->getActionGroup('managecurrencyrates')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Managecurrencyrates::ACTION_CURRENCY_RATES_SAVE
        );
        if (!is_array($Rates)) {
            $Rates = array($Rates);
        }
        if ($log){
            $log->addDetails($Rates);
        }
    }

    public function CurrencyRatesFetch($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('managecurrencyrates')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Managecurrencyrates::ACTION_CURRENCY_RATES_FETCH
        );
    }
}