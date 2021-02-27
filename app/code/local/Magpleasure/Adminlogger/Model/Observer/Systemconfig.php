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
class Magpleasure_Adminlogger_Model_Observer_Systemconfig extends Magpleasure_Adminlogger_Model_Observer
{
    /**
     * Enter description here...
     *
     * @param unknown_type $a
     * @param unknown_type $b
     * @return int
     */
    protected function _sort($a, $b)
    {
        return (int)$a->sort_order < (int)$b->sort_order ? -1 : ((int)$a->sort_order > (int)$b->sort_order ? 1 : 0);
    }

    /**
     * Enter description here...
     *
     * @param string $code
     * @return boolean
     */
    public function checkSectionPermissions($code=null)
    {
        static $permissions;

        if (!$code or trim($code) == "") {
            return false;
        }

        if (!$permissions) {
            $permissions = Mage::getSingleton('admin/session');
        }

        $showTab = false;
        if ( $permissions->isAllowed('system/config/'.$code) ) {
            $showTab = true;
        }
        return $showTab;
    }

    /**
     * Enter description here...
     *
     */
    protected function _prepareSystemSection()
    {
        $current = Mage::app()->getRequest()->getParam('section');
        $websiteCode = Mage::app()->getRequest()->getParam('website');
        $storeCode = Mage::app()->getRequest()->getParam('store');

        $url = Mage::getModel('adminhtml/url');

        $configFields = Mage::getSingleton('adminhtml/config');
        $sections = $configFields->getSections($current);
        $tabs     = (array)$configFields->getTabs()->children();


        $sections = (array)$sections;

        usort($sections, array($this, '_sort'));
        usort($tabs, array($this, '_sort'));

        foreach ($sections as $section) {

            $hasChildren = $configFields->hasChildren($section, $websiteCode, $storeCode);

            //$code = $section->getPath();
            $code = $section->getName();

            $sectionAllowed = $this->checkSectionPermissions($code);
            if ((empty($current) && $sectionAllowed)) {

                $current = $code;
                Mage::app()->getRequest()->setParam('section', $current);
            }
        }
        return $this;
    }

    protected function _getSavedConfig()
    {
        $result = array();
        $post = Mage::app()->getRequest()->getPost();
        $section = Mage::app()->getRequest()->getParam('section');

        if (isset($post['groups']) && is_array($post['groups'])){
            foreach ($post['groups'] as $group => $gArray){
                if (is_array($gArray)){
                    foreach ($gArray as $extension => $eArray){
                        if (is_array($eArray)){
                            foreach ($eArray as $key => $value){
                                $path = "{$section}/{$group}/{$key}";
                                if (isset($value['value']) && !is_array($value['value'])){
                                    $result[$path] = $value['value'];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function SystemConfigSave($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('systemconfig')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Systemconfig::ACTION_SYSTEM_CONFIG_SAVE
        );
        $this->_prepareSystemSection();
        $section = Mage::app()->getRequest()->getParam('section');

        if ($log){

            $values = $this->_getSavedConfig();
            $origValues = array();

            foreach ($values as $path => $value){
                $origValues[$path] = Mage::getStoreConfig($path, $this->_getStore());
            }

            $log->addDetails(array(array('attribute_code'=>'__section__', 'from'=> null, 'to'=> $section)));
            $log->addDetails($this->_helper()->getCompare()->diff($values, $origValues));
        }
    }

    public function SystemConfigLoad($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('systemconfig')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Systemconfig::ACTION_SYSTEM_CONFIG_LOAD
        );

        $this->_prepareSystemSection();
        $section = Mage::app()->getRequest()->getParam('section');

        if ($log){
            $log->addDetails(array(array('attribute_code'=>'__section__', 'from'=> null, 'to'=> $section)));
        }
    }

}