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
class Magpleasure_Adminlogger_Model_Observer_Catalogpricerules extends Magpleasure_Adminlogger_Model_Observer
{

    public function CatalogPriceRulesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogpricerules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogpricerules::ACTION_PRICE_RULES_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function CatalogPriceRulesSave($event)
    {
        $rule = $event->getRule();
        $log = $this->createLogRecord(
            $this->getActionGroup('catalogpricerules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogpricerules::ACTION_PRICE_RULES_SAVE,
            $rule->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($rule->getData(), $rule->getOrigData())
            );
        }
    }

    public function CatalogPriceRulesDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogpricerules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogpricerules::ACTION_PRICE_RULES_DELETE
        );
    }

    public function CatalogPriceRulesApply($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('catalogpricerules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Catalogpricerules::ACTION_PRICE_RULES_APPLY
        );
    }
}