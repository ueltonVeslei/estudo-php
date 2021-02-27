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
class Magpleasure_Adminlogger_Model_Observer_Cacheaction extends Magpleasure_Adminlogger_Model_Observer
{

    public function CacheFlushAll($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('cacheaction')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cacheaction::ACTION_CACHE_FLUSH_ALL
        );
    }

    public function CacheFlushSystem($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('cacheaction')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cacheaction::ACTION_CACHE_FLUSH_SYSTEM
        );

    }

    public function CacheCleanMedia($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('cacheaction')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cacheaction::ACTION_CACHE_CLEAN_MEDIA
        );
    }

    public function CacheCleanCatalogImage($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('cacheaction')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cacheaction::ACTION_CACHE_CLEAN_CATALOG_IMAGE
        );
    }

    public function CacheMassRefresh($event)
    {
        $types = Mage::app()->getRequest()->getParam('types');
        $log = $this->createLogRecord(
            $this->getActionGroup('cacheaction')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cacheaction::ACTION_MASS_REFRESH
        );

        if (!is_array($types)) {
            $type = array($types);
        }
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($types));
        }
    }

    public function CacheMassEnable($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('cacheaction')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cacheaction::ACTION_MASS_ENABLE
        );

        $types = Mage::app()->getRequest()->getParam('types');
        if (!is_array($types)) {
            $types = array($types);
        }
        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($types));
        }
    }

    public function CacheMassDisable($event)
    {
        $log = $this->createLogRecord(
            $this->getActionGroup('cacheaction')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Cacheaction::ACTION_MASS_DISABLE
        );

        $types = Mage::app()->getRequest()->getParam('types');
        if (!is_array($types)) {
            $types = array($types);
        }

        if ($log){
            $log->addDetails($this->_prepareDetailsFromArray($types));
        }
    }
}