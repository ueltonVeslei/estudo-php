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
class Magpleasure_Adminlogger_Model_Actiongroup_Cacheaction extends Magpleasure_Adminlogger_Model_Actiongroup_Abstract
{
    protected $_typeValue = 7;
    const ACTION_CACHE_FLUSH_ALL = 1;
    const ACTION_CACHE_FLUSH_SYSTEM = 2;
    const ACTION_CACHE_CLEAN_MEDIA = 3;
    const ACTION_CACHE_CLEAN_CATALOG_IMAGE = 4;
    const ACTION_MASS_REFRESH = 5;
    const ACTION_MASS_ENABLE = 6;
    const ACTION_MASS_DISABLE = 7;

    public function getLabel()
    {
        return $this->_helper()->__("Cache Management");
    }

    public function getDetailsRenderer($type = false)
    {
        return 'list';
    }

    protected function _getActions()
    {
        return array(
            array('value' => self::ACTION_CACHE_FLUSH_ALL, 'label' => $this->_helper()->__("Flush All")),
            array('value' => self::ACTION_CACHE_FLUSH_SYSTEM, 'label' => $this->_helper()->__("Flush System")),
            array('value' => self::ACTION_CACHE_CLEAN_MEDIA, 'label' => $this->_helper()->__("Clean Media")),
            array('value' => self::ACTION_CACHE_CLEAN_CATALOG_IMAGE, 'label' => $this->_helper()->__("Clean Catalog Image")),
            array('value' => self::ACTION_MASS_REFRESH, 'label' => $this->_helper()->__("Mass Refresh")),
            array('value' => self::ACTION_MASS_ENABLE, 'label' => $this->_helper()->__("Mass Enable")),
            array('value' => self::ACTION_MASS_DISABLE, 'label' => $this->_helper()->__("Mass Disable")),
        );
    }


}