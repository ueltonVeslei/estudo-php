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
class Magpleasure_Adminlogger_Model_Actiongroup_Adminsession extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{

    protected $_typeValue = 1;

    const ACTION_LOGIN_SUCCESS = 1;
    const ACTION_LOGIN_FAILED = 2;

    public function getLabel()
    {
        return $this->_helper()->__("Admin Session");
    }


    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_LOGIN_SUCCESS, 'label' => $this->_helper()->__("Login Success")),
            array('value' => self::ACTION_LOGIN_FAILED, 'label' => $this->_helper()->__("Login Failed")),
        );
    }
}