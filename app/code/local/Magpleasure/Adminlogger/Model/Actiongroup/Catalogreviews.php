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
class Magpleasure_Adminlogger_Model_Actiongroup_Catalogreviews extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 29;
    const ACTION_REVIEWS_LOAD = 1;
    const ACTION_REVIEWS_SAVE = 2;
    const ACTION_REVIEWS_DELETE = 3;

    public function getLabel()
    {
        return $this->_helper()->__("Catalog Reviews");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_REVIEWS_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_REVIEWS_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_REVIEWS_DELETE, 'label' => $this->_helper()->__("Delete")),
        );
    }


    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'review/review';
    }

    public function getFieldKey()
    {
        return 'title';
    }

    public function getUrlPath()
    {
        return 'adminhtml/catalog_product_review/edit';
    }

    public function getUrlIdKey()
    {
        return 'id';
    }

}
