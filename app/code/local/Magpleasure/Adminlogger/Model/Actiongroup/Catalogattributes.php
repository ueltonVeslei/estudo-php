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
class Magpleasure_Adminlogger_Model_Actiongroup_Catalogattributes extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 26;
    const ACTION_CATALOG_ATTRIBUTES_LOAD = 1;
    const ACTION_CATALOG_ATTRIBUTES_SAVE = 2;
    const ACTION_CATALOG_ATTRIBUTES_DELETE = 3;

    public function getLabel()
    {
        return $this->_helper()->__("Catalog Attributes");
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_CATALOG_ATTRIBUTES_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_CATALOG_ATTRIBUTES_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_CATALOG_ATTRIBUTES_DELETE, 'label' => $this->_helper()->__("Delete")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'catalog/resource_eav_attribute';
    }

    public function getFieldKey()
    {
        return 'attribute_code';
    }

    public function getUrlPath()
    {
        return 'adminhtml/catalog_product_attribute/edit';
    }

    public function getUrlIdKey()
    {
        return 'attribute_id';
    }
}
