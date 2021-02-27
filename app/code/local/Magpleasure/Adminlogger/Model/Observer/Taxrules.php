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
class Magpleasure_Adminlogger_Model_Observer_Taxrules extends Magpleasure_Adminlogger_Model_Observer
{

    public function TaxRulesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('taxrules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Taxrules::ACTION_TAX_RULES_LOAD,
            Mage::app()->getRequest()->getParam('rule')
        );
    }

    public function TaxRulesSave($event)
    {
        $rule = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('taxrules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Taxrules::ACTION_TAX_RULES_SAVE,
            $rule->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($rule->getData(), $rule->getOrigData())
            );
        }
    }

    public function TaxRulesDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('taxrules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Taxrules::ACTION_TAX_RULES_DELETE
        );
    }
}