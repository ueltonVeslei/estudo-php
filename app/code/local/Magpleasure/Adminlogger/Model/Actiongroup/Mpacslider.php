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
class Magpleasure_Adminlogger_Model_Actiongroup_Mpacslider extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 54;
    const ACTION_SLIDER_LOAD = 1;
    const ACTION_SLIDER_SAVE = 2;
    const ACTION_SLIDER_DELETE = 3;
    const ACTION_SLIDER_ADDSLIDE = 4;

    public function getLabel()
    {
        return $this->_helper()->__("Magpleasure Active Content Slide Rotators");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_SLIDER_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_SLIDER_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_SLIDER_DELETE, 'label' => $this->_helper()->__("Delete")),
            array('value' => self::ACTION_SLIDER_ADDSLIDE, 'label' => $this->_helper()->__("Add Slides")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'activecontent/block';
    }

    public function getFieldKey()
    {
        return 'name';
    }

    public function getUrlPath()
    {
        return 'activecontent_admin/admin_block/edit';
    }

    public function getUrlIdKey()
    {
        return 'id';
    }
}