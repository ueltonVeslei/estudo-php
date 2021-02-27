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
class Magpleasure_Adminlogger_Model_Observer_Shoppingcartpricerules extends Magpleasure_Adminlogger_Model_Observer
{

    public function PriceRulesLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('shoppingcartpricerules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Shoppingcartpricerules::ACTION_PRICE_RULES_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function PriceRulesSave($event)
    {
        $priceRule = $event->getRule();
        $log = $this->createLogRecord(
            $this->getActionGroup('shoppingcartpricerules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Shoppingcartpricerules::ACTION_PRICE_RULES_SAVE,
            $priceRule->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($priceRule->getData(), $priceRule->getOrigData())
            );
        }
    }

    public function PriceRulesDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('shoppingcartpricerules')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Shoppingcartpricerules::ACTION_PRICE_RULES_DELETE
        );
    }
}