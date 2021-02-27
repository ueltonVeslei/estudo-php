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
class Magpleasure_Adminlogger_Model_Actiongroup_Indexmanagement extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 42;
    const ACTION_INDEX_MANAGEMENT_MASS_REINDEX = 1;
    const ACTION_INDEX_MANAGEMENT_MASS_CHANGE_MODE = 2;
    const ACTION_INDEX_MANAGEMENT_REINDEX = 3;
    const ACTION_INDEX_MANAGEMENT_SAVE = 4;

    public function getLabel()
    {
        return $this->_helper()->__("Index Management");
    }

    public function getDetailsRenderer($type = false)
    {
        return 'list';
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_INDEX_MANAGEMENT_MASS_REINDEX, 'label' => $this->_helper()->__("Mass Reindex")),
            array('value' => self::ACTION_INDEX_MANAGEMENT_MASS_CHANGE_MODE, 'label' => $this->_helper()->__("Mass Change Mode")),
            array('value' => self::ACTION_INDEX_MANAGEMENT_REINDEX, 'label' => $this->_helper()->__("Reindex")),
            array('value' => self::ACTION_INDEX_MANAGEMENT_SAVE, 'label' => $this->_helper()->__("Save")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'index/process';
    }

    public function getFieldKey()
    {
        return 'indexer_code';
    }

    public function getUrlPath()
    {
        return 'adminhtml/process/edit';
    }

    public function getUrlIdKey()
    {
        return 'process';
    }
}