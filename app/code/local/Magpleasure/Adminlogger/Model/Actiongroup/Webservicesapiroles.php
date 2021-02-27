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
class Magpleasure_Adminlogger_Model_Actiongroup_Webservicesapiroles extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 32;
    const ACTION_API_ROLES_LOAD = 1;
    const ACTION_API_ROLES_SAVE = 2;
    const ACTION_API_ROLES_DELETE = 3;

    public function getLabel()
    {
        return $this->_helper()->__("Webservices API Roles");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_API_ROLES_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_API_ROLES_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_API_ROLES_DELETE, 'label' => $this->_helper()->__("Delete")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'api/roles';
    }

    public function getFieldKey()
    {
        return 'role_name';
    }

    public function getUrlPath()
    {
        return 'adminhtml/api_role/editrole';
    }

    public function getUrlIdKey()
    {
        return 'rid';
    }
}
