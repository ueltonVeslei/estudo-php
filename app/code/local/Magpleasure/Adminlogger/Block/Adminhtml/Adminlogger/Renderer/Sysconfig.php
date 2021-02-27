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
class Magpleasure_Adminlogger_Block_Adminhtml_Adminlogger_Renderer_Sysconfig
    extends Magpleasure_Adminlogger_Block_Adminhtml_Adminlogger_Renderer_Default
{

    protected $_details = array();

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("adminlogger/renderer/sysconfig.phtml");
    }

    protected function _beforeToHtml()
    {
        $this->_prepareDetails();
        return parent::_beforeToHtml();
    }

    /**
     * Prepare Details
     *
     * @return Magpleasure_Adminlogger_Block_Adminhtml_Adminlogger_Renderer_Sysconfig
     */
    protected function _prepareDetails()
    {
        foreach ($this->getDetails() as $detail){
            if ($detail->getAttributeCode() == '__section__'){
                $this->setSectionName($detail->getTo());
            } else {
                $this->_details[] = $detail;
            }
        }
        return $this;
    }



    public function getSectionName()
    {
        $sectionName = $this->getData('section_name');
        $configFields = Mage::getSingleton('adminhtml/config');
        $sections = $configFields->getSections();
        $sections = (array)$sections;
        foreach ($sections as $section) {
            $code = $section->getName();
            $helperName = $configFields->getAttributeModule($section);
            $label = Mage::helper($helperName)->__((string)$section->label);
            if ($code == $sectionName) {
                return $label;
            }
        }
        return $sectionName;
    }

    public function getSectionDetails()
    {
        return $this->_details;
    }
}