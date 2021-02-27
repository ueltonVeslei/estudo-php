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
class Magpleasure_Adminlogger_Model_Actiongroup_Systemconfig extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 46;
    const ACTION_SYSTEM_CONFIG_LOAD = 1;
    const ACTION_SYSTEM_CONFIG_SAVE = 2;

    public function getLabel()
    {
        return $this->_helper()->__("System Configuration");
    }

    public function getDetailsRenderer($type = false)
    {
        return 'sysconfig';
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_SYSTEM_CONFIG_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_SYSTEM_CONFIG_SAVE, 'label' => $this->_helper()->__("Save")),
        );
    }


}