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
class Magpleasure_Adminlogger_Model_Actiongroup_Catalogtags extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 15;
    const ACTION_CATALOG_TAGS_LOAD = 1;
    const ACTION_CATALOG_TAGS_SAVE = 2;
    const ACTION_CATALOG_TAGS_DELETE = 3;

    public function getLabel()
    {
        return $this->_helper()->__("Catalog Tags");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_CATALOG_TAGS_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_CATALOG_TAGS_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_CATALOG_TAGS_DELETE, 'label' => $this->_helper()->__("Delete")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'tag/tag';
    }

    public function getFieldKey()
    {
        return 'name';
    }

    public function getUrlPath()
    {
        return 'adminhtml/tag/edit';
    }

    public function getUrlIdKey()
    {
        return 'tag_id';
    }
}
