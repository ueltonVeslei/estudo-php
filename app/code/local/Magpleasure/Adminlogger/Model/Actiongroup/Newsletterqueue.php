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
class Magpleasure_Adminlogger_Model_Actiongroup_Newsletterqueue extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 44;

    const ACTION_NEWSLETTER_QUEUE_LOAD = 1;
    const ACTION_NEWSLETTER_QUEUE_SAVE = 2;
    const ACTION_NEWSLETTER_QUEUE_CREATE = 3;

    public function getLabel()
    {
        return $this->_helper()->__("Newsletter Queue");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_NEWSLETTER_QUEUE_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_NEWSLETTER_QUEUE_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_NEWSLETTER_QUEUE_CREATE, 'label' => $this->_helper()->__("Create")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'newsletter/queue';
    }

    public function getFieldKey()
    {
        return 'newsletter_subject';
    }

    public function getUrlPath()
    {
        return 'adminhtml/newsletter_queue/edit';
    }

    public function getUrlIdKey()
    {
        return 'id';
    }
}
