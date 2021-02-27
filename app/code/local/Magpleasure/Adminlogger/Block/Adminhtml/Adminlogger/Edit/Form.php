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
class Magpleasure_Adminlogger_Block_Adminhtml_Adminlogger_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('adminlogger/info.phtml');
    }

    /**
     * Log
     *
     * @return Magpleasure_Adminlogger_Model_Log|Varien_Object
     */
    public function getLog()
    {
        if ($log = Mage::registry('adminlogger_data')){
            return $log;
        }
        return new Varien_Object();
    }

    public function getActionTime()
    {
        if ($this->getLog()->getActionTime()){
            $datetime = new Zend_Date($this->getLog()->getActionTime(), Zend_Date::ISO_8601, Mage::app()->getLocale()->getLocaleCode());
            $datetime->setTimezone(Mage::getStoreConfig('general/locale/timezone'));

            return $datetime->toString(Zend_Date::DATETIME_LONG);
        }
        return false;
    }

    public function getActionGroup()
    {
        return $this->getLog()->getActionGroupLabel();
    }

    public function getActionType()
    {
        return $this->getLog()->getActionTypeLabel();
    }

    public function getUserName()
    {
        return $this->getLog()->getUserLabel();
    }

    public function getShowDetails()
    {
        return $this->_helper()->getConfDetailsEnabled() && $this->getDetails()->getSize();
    }

    public function getDetails()
    {
        return $this->getLog()->getDetailsCollection();
    }

    public function getRenderedDetails()
    {
        /** @var Magpleasure_Adminlogger_Model_Actiongroup_Abstract $actionGroup  */
        $actionGroup = $this->_helper()->getActionGroup($this->getLog()->getActionGroup());
        $actionType = $this->getLog()->getActionType();
        if ($actionGroup){
            $rendererCode = $actionGroup->getDetailsRenderer($actionType);
            /** @var Magpleasure_Adminlogger_Block_Adminhtml_Adminlogger_Renderer_Default $renderer  */
            $renderer = $this->getLayout()->createBlock("adminlogger/adminhtml_adminlogger_renderer_{$rendererCode}");
            if ($renderer){
                $renderer->setLog($this->getLog());
                return $renderer->toHtml();
            }
        }
        return false;
    }

    public function getIp()
    {
        return long2ip($this->getLog()->getRemoteAddr());
    }

    public function getStoreName()
    {
        $store = Mage::getModel('core/store')->load($this->getLog()->getStoreId());
        if ($store->getId()){
            return $store->getName();
        }
        return $this->__("All Store Views");
    }


    public function getWebsiteName()
    {
        $website = Mage::getModel('core/website')->load($this->getLog()->getWebsiteId());
        if ($website->getId()){
            return $website->getName();
        }
        return "";
    }

    public function getDisplayEntity()
    {
        $entityId = $this->getLog()->getEntityId();
        return  ($entityId && $this->_helper()->getActionGroup($this->getLog()->getActionGroup())->canDisplayEntity());
    }

    public function getEntityUrl()
    {
        $group = $this->_helper()->getActionGroup($this->getLog()->getActionGroup());
        return $this->getUrl($group->getUrlPath(), array( $group->getUrlIdKey() => $this->getLog()->getEntityId()));
    }

    public function getEntityExists()
    {
        $group = $this->_helper()->getActionGroup($this->getLog()->getActionGroup());
        $entity = Mage::getModel($group->getModelType())->load($this->getLog()->getEntityId());
        return !!$entity->getId();
    }

    public function getEntityName()
    {
        $group = $this->_helper()->getActionGroup($this->getLog()->getActionGroup());
        $entity = Mage::getModel($group->getModelType())->load($this->getLog()->getEntityId());
        return $entity->getId() ? sprintf($group->getFieldPattern(), $entity->getData($group->getFieldKey())) : $this->__("Not found");
    }

    public function getFullList()
    {
        $html = "";
        foreach ($this->_helper()->getActionGroups() as $value=>$label){
            $actionsHtml = "";
            $actions = $this->_helper()->getActionGroup($value)->getOptions();
            foreach ($actions as $key=>$actionLabel){
                $actionsHtml .= "<li>".$actionLabel."</li>";
            }
            $actionsHtml = "<ul>".$actionsHtml."</ul>";
            $html .= "<li>".$label.$actionsHtml."</li>";
        }
        return "<ul>".$html."</ul>";
    }

}