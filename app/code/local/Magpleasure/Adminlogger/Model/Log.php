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

class Magpleasure_Adminlogger_Model_Log extends Mage_Core_Model_Abstract
{
    const SYSTEM_LOG_ALL_USERS = 'mp_adminlogger_log_all_users';

    const SYSTEM_LOG_ALL_ACTIONS = 'mp_adminlogger_log_all_actions';

    protected $_details;

    public function _construct()
    {
        parent::_construct();
        $this->_init('adminlogger/log');
    }

    /**
     * Helper
     *
     * @return Magpleasure_Adminlogger_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('adminlogger');
    }

    public function addDetails(array $details, $entityId = null)
    {
        if (!$this->_helper()->getConfLogEnabled() || !$this->_helper()->getConfDetailsEnabled()){
            return $this;
        }

        foreach ($details as $dataArray) {

            $data = new Varien_Object($dataArray);
            $detail = Mage::getModel('adminlogger/details');
            $detail->setLogId($this->getId())
                ->setEntityId($entityId)
                ->setAttributeCode($data->getAttributeCode())
                ->setFrom($data->getFrom())
                ->setTo($data->getTo())
                ->save();
        }
        return $this;
    }

    public function getActionGroupLabel()
    {
        return $this->_helper()->getActionGroup($this->getActionGroup())->getLabel();
    }

    public function getActionTypeLabel()
    {
        return $this->_helper()->getActionGroup($this->getActionGroup())->getActionLabel($this->getActionType());
    }

    public function getUserLabel()
    {
        return $this->_helper()->getUserName($this->getUserId());
    }

    /**
     * Details Collection
     *
     * @return Magpleasure_Adminlogger_Model_Mysql4_Details_Collection
     */
    public function getDetailsCollection()
    {
        if (!$this->_details){
            /** @var Magpleasure_Adminlogger_Model_Mysql4_Details_Collection $details  */
            $details = Mage::getModel('adminlogger/details')->getCollection();
            $details->addFieldToFilter('log_id', $this->getId());
            $this->_details = $details;
        }
        return $this->_details;
    }

}