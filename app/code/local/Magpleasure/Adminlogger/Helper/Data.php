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
class Magpleasure_Adminlogger_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCurrentTime()
    {
        $CurrentTime = date("Y-m-d H:i:s");
        return $CurrentTime;
    }

    /**
     * @return Magpleasure_Adminlogger_Helper_Compare
     */
    public function getCompare()
    {
        return Mage::helper('adminlogger/compare');
    }

    public function getActionGroups()
    {
        $path = Mage::getBaseDir('code') . DS . "local" . DS . "Magpleasure" . DS . "Adminlogger" . DS . "Model" . DS . "Actiongroup";
        $results = array();
        if (file_exists($path) && is_dir($path)) {
            foreach (scandir($path) as $fileName) {
                if (!in_array($fileName, array('.', '..', 'Abstract.php', 'Interface.php'))) {
                    $className = "Magpleasure_Adminlogger_Model_Actiongroup_" . ucfirst(str_replace(".php", "", strtolower($fileName)));
                    if (class_exists($className)) {
                        /** @var Magpleasure_Adminlogger_Model_Actiongroup_Abstract $actionGroup  */
                        $actionGroup = new $className();
                        $results[$actionGroup->getValue()] = $actionGroup->getLabel();

                    }
                }
            }
        }
        return $results;
    }

    /**
     * All Action Types by Group in Array
     *
     * @return array
     */
    public function getAllActionTypes()
    {
        $types = array();
        foreach ($this->getActionGroups() as $groupValue => $groupLabel){
            $group = $this->getActionGroup($groupValue);
            $types[$groupValue] = $group->getOptions();
        }
        return $types;
    }

    /**
     * Action Group
     *
     * @param int $searchValue
     * @return Magpleasure_Adminlogger_Model_Actiongroup_Abstract
     */
    public function getActionGroup($searchValue)
    {
        $path = Mage::getBaseDir('code') . DS . "local" . DS . "Magpleasure" . DS . "Adminlogger" . DS . "Model" . DS . "Actiongroup";
        if (file_exists($path) && is_dir($path)) {
            foreach (scandir($path) as $fileName) {
                if (!in_array($fileName, array('.', '..', 'Abstract.php', 'Interface.php'))) {
                    $className = "Magpleasure_Adminlogger_Model_Actiongroup_" . ucfirst(str_replace(".php", "", strtolower($fileName)));
                    if (class_exists($className)) {
                        /** @var Magpleasure_Adminlogger_Model_Actiongroup_Abstract $actionGroup  */
                        $actionGroup = new $className();
                        if ($actionGroup->getValue() == $searchValue) {
                            return $actionGroup;
                        }
                    }
                }
            }
        }
        return new Varien_Object();
    }

    public function getUserName($userId)
    {
        foreach ($this->getUsers() as $uId => $userName){
            if ($uId == $userId){
                return $userName;
            }
        }
        return "";
    }

    public function getUsers()
    {
        $results = array();
        /** @var Mage_Admin_Model_Resource_User_Collection $users  */
        $users = Mage::getModel('admin/user')->getCollection();
        foreach ($users as $user) {
            /** @var Mage_Admin_Model_User $user */
            $name = $this->__("%s %s (%s)", $user->getFirstname(), $user->getLastname(), $user->getUsername());
            $results[$user->getId()] = $name;
        }
        return $results;
    }


    public function getStores()
    {
        $results = array(
            0 => $this->__("All Store Views")
        );

        $stores = Mage::getModel('core/store')->getCollection();
        foreach ($stores as $store) {
            $results[$store->getId()] = $store->getName();
        }
        return $results;
    }

    /**
     * Extension is enabled
     *
     * @return mixed
     */
    public function getConfLogEnabled()
    {
        return Mage::getStoreConfig('adminlogger/general/enabled');
    }

    /**
     * Details log is enabled
     *
     * @return mixed
     */
    public function getConfDetailsEnabled()
    {
        return Mage::getStoreConfig('adminlogger/general/save_details');
    }

    /**
     * Details log is enabled
     *
     * @return mixed
     */
    public function getConfKeepXDays()
    {
        return Mage::getStoreConfig('adminlogger/general/keep_days');
    }

    /**
     * Need log this group of actions
     *
     * @param int $actionGroup
     * @return bool
     */
    public function needLogForActionGroup($actionGroup)
    {
        $enabledActions = explode(",", Mage::getStoreConfig('adminlogger/general/action_groups'));
        return in_array(Magpleasure_Adminlogger_Model_Log::SYSTEM_LOG_ALL_ACTIONS, $enabledActions) ||
               in_array($actionGroup, $enabledActions);
    }

    /**
     * Need log this group of actions
     *
     * @param int $userId
     * @return bool
     */
    public function needLogForUser($userId)
    {
        $enabledUsers = explode(",", Mage::getStoreConfig('adminlogger/general/users'));
        return  in_array(Magpleasure_Adminlogger_Model_Log::SYSTEM_LOG_ALL_USERS, $enabledUsers) ||
                in_array($userId, $enabledUsers);
    }
}