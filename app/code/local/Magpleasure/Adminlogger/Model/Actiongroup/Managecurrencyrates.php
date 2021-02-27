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
class Magpleasure_Adminlogger_Model_Actiongroup_Managecurrencyrates extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 43;
    const ACTION_CURRENCY_RATES_VIEW = 1;
    const ACTION_CURRENCY_RATES_SAVE = 2;
    const ACTION_CURRENCY_RATES_FETCH = 3;

    public function getLabel()
    {
        return $this->_helper()->__("Manage Currency Rates");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_CURRENCY_RATES_VIEW, 'label' => $this->_helper()->__("View")),
            array('value' => self::ACTION_CURRENCY_RATES_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_CURRENCY_RATES_FETCH, 'label' => $this->_helper()->__("Import")),
        );
    }

}
