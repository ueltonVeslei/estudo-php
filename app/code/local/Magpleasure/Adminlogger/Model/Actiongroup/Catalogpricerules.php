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
class Magpleasure_Adminlogger_Model_Actiongroup_Catalogpricerules extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 30;
    const ACTION_PRICE_RULES_LOAD = 1;
    const ACTION_PRICE_RULES_SAVE = 2;
    const ACTION_PRICE_RULES_DELETE = 3;
    const ACTION_PRICE_RULES_APPLY = 4;

    public function getLabel()
    {
        return $this->_helper()->__("Catalog Price Rules");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_PRICE_RULES_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_PRICE_RULES_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_PRICE_RULES_DELETE, 'label' => $this->_helper()->__("Delete")),
            array('value' => self::ACTION_PRICE_RULES_APPLY, 'label' => $this->_helper()->__("Apply")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'catalogrule/rule';
    }

    public function getFieldKey()
    {
        return 'name';
    }

    public function getUrlPath()
    {
        return 'adminhtml/promo_catalog/edit';
    }

    public function getUrlIdKey()
    {
        return 'id';
    }

}
