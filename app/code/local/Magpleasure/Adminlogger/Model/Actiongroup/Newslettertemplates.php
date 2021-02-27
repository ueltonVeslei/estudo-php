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
class Magpleasure_Adminlogger_Model_Actiongroup_Newslettertemplates extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 25;
    const ACTION_NEWSLETTER_TEMPLATES_LOAD = 1;
    const ACTION_NEWSLETTER_TEMPLATES_SAVE = 2;
    const ACTION_NEWSLETTER_TEMPLATES_DELETE = 3;

    public function getLabel()
    {
        return $this->_helper()->__("Newsletter templates");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_NEWSLETTER_TEMPLATES_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_NEWSLETTER_TEMPLATES_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_NEWSLETTER_TEMPLATES_DELETE, 'label' => $this->_helper()->__("Delete")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'newsletter/template';
    }

    public function getFieldKey()
    {
        return 'template_subject';
    }

    public function getUrlPath()
    {
        return 'adminhtml/newsletter_template/edit';
    }

    public function getUrlIdKey()
    {
        return 'id';
    }
}
