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
class Magpleasure_Adminlogger_Model_Details extends Mage_Core_Model_Abstract
{
    const DIRECTION_FROM = 1;
    const DIRECTION_TO = 2;

    /**
     * @return Magpleasure_Adminlogger_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('adminlogger');
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('adminlogger/details');
    }

    public function getDiffRequired()
    {
        $from = $this->getData('from');
        $to = $this->getData('to');
        return (is_string($from) && (strlen($from) > 6)) || (is_string($to) && (strlen($to) > 6));
    }

    public function getTo($needColorize = false)
    {
        if (!$needColorize){
            return $this->getData('to');
        }

        $from = $this->getData('from');
        $to = $this->getData('to');

        if ($this->getDiffRequired()){
            return $this->_helper()->getCompare()->htmlToDiff(trim($from), trim($to), true);
        } else {
            return $to;
        }
    }

    public function getFrom($needColorize = false)
    {
        if (!$needColorize){
            return $this->getData('from');
        }

        $from = $this->getData('from');
        $to = $this->getData('to');

        if ($this->getDiffRequired() && $from){
            return $this->_helper()->getCompare()->htmlFromDiff(trim($from), trim($to), true);
        } else {
            return $from;
        }
    }
}