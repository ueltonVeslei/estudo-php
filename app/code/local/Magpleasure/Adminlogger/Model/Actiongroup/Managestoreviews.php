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
class Magpleasure_Adminlogger_Model_Actiongroup_Managestoreviews extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 38;
    const ACTION_MANAGE_STORE_VIEWS_LOAD = 1;
    const ACTION_MANAGE_STORE_VIEWS_SAVE = 2;
    const ACTION_MANAGE_STORE_VIEWS_DELETE = 3;

    public function getLabel()
    {
        return $this->_helper()->__("Manage Store Views");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_MANAGE_STORE_VIEWS_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_MANAGE_STORE_VIEWS_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_MANAGE_STORE_VIEWS_DELETE, 'label' => $this->_helper()->__("Delete")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'core/store';
    }

    public function getFieldKey()
    {
        return 'name';
    }

    public function getUrlPath()
    {
        return 'adminhtml/system_store/editStore';
    }

    public function getUrlIdKey()
    {
        return 'store_id';
    }

}
