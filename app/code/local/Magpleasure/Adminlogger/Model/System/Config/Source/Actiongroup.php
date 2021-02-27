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
class Magpleasure_Adminlogger_Model_System_Config_Source_Actiongroup extends Mage_Core_Model_Abstract
{
    /**
     * Helper
     *
     * @return Magpleasure_Adminlogger_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('adminlogger');
    }

    public function toOptionArray()
    {
        $result = array();

        $result[] = array('value'=>Magpleasure_Adminlogger_Model_Log::SYSTEM_LOG_ALL_ACTIONS, 'label'=>$this->_helper()->__("All Actions"));
        foreach ($this->_helper()->getActionGroups() as $key=>$value){
            $result[] = array('value'=>$key, 'label'=>$value);
        }
        return $result;
    }
}