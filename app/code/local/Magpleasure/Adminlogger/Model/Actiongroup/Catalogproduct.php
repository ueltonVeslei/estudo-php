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
class Magpleasure_Adminlogger_Model_Actiongroup_Catalogproduct extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 2;
    const ACTION_PRODUCT_LOAD = 1;
    const ACTION_PRODUCT_SAVE = 2;
    const ACTION_PRODUCT_DELETE = 3;
    const ACTION_PRODUCT_MASS_ATTRUBUTE_UPDATE = 4;
    const ACTION_PRODUCT_CREATE = 5;

    public function getLabel()
    {
        return $this->_helper()->__("Catalog Product");
    }

    public function getDetailsRenderer($type = false)
    {
        if ($type == self::ACTION_PRODUCT_MASS_ATTRUBUTE_UPDATE){
            return 'massupdate';
        } else {
            return parent::getDetailsRenderer($type);
        }
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_PRODUCT_CREATE, 'label' => $this->_helper()->__("Create")),
            array('value' => self::ACTION_PRODUCT_LOAD, 'label' => $this->_helper()->__("Load")),
            array('value' => self::ACTION_PRODUCT_SAVE, 'label' => $this->_helper()->__("Save")),
            array('value' => self::ACTION_PRODUCT_DELETE, 'label' => $this->_helper()->__("Delete")),
            array('value' => self::ACTION_PRODUCT_MASS_ATTRUBUTE_UPDATE, 'label' => $this->_helper()->__("Mass Attribute Update")),
        );
    }

    public function canDisplayEntity()
    {
        return true;
    }

    public function getModelType()
    {
        return 'catalog/product';
    }

    public function getFieldKey()
    {
        return 'name';
    }

    public function getUrlPath()
    {
        return 'adminhtml/catalog_product/edit';
    }

    public function getUrlIdKey()
    {
        return 'id';
    }


}
