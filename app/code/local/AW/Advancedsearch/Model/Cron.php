<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedsearch
 * @version    1.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AW_Advancedsearch_Model_Cron
{
    const LOCK = 'awarpcronlock';
    const LOCKTIME = 300; // 5 minutes
    const LAST_EXEC = 'awarplastexec';
    const DEADTIME = 10; // 10 seconds

    protected static $_lockTime = null;

    public function runJobs()
    {
        if(self::checkLastExec() && self::checkLock()) {
            self::_rebuildIndexes();
            Mage::app()->removeCache(self::LOCK);
        }
        self::saveLastExec();
    }

    protected static function _getLockTime()
    {
        if(self::$_lockTime === null) {
            $lockTime = intval(Mage::helper('awadvancedsearch/config')->getSphinxFullReindex());
            $lockTime = $lockTime ? $lockTime * 60 * 60 : self::LOCKTIME;
            self::$_lockTime = $lockTime;
        }
        return self::$_lockTime;
    }

    public static function saveLastExec()
    {
        Mage::app()->saveCache(time(), self::LAST_EXEC, array(), self::DEADTIME);
    }

    public static function checkLastExec()
    {
        if(($time = Mage::app()->loadCache(self::LAST_EXEC))) {
            if((time() - $time) < self::DEADTIME)
                return false;
        }
        return true;
    }

    public static function _formatTime($time)
    {
        $formated = '';
        if($time < 120) {
            $formated = $time.' seconds';
        } elseif($time < 120) {
            $formated = intval($time / 60).' minutes';
        } else {
            $formated = intval($time / 3600).' hours';
        }
        return $formated;
    }

    public static function checkLock()
    {
        if(($time = Mage::app()->loadCache(self::LOCK))) {
            if((time() - $time) < self::_getLockTime()) {
                self::_getLogHelper()->log(__CLASS__, 'Next reindex after '.(self::_formatTime(self::_getLockTime() - (time() - $time))));
                return false;
            }
        }
        Mage::app()->saveCache(time(), self::LOCK, array(), self::_getLockTime());
        return true;
    }

    protected static function _getActiveIndexes()
    {
        $indexes = Mage::getModel('awadvancedsearch/catalogindexes')->getCollection();
        $indexes->addStatusFilter();
        return $indexes;
    }

    protected function _rebuildIndexes()
    {
        if(Mage::helper('awadvancedsearch/config')->getGeneralEnabled()) {
            self::_getLogHelper()->log(__CLASS__, 'Starting rebuilding indexes');
            foreach(self::_getActiveIndexes() as $index) {
                $index->setData('state', AW_Advancedsearch_Model_Source_Catalogindexes_State::REINDEX_REQUIRED)
                      ->save()
                      ->callAfterLoad();
                $indexer = $index->getIndexer();
                if($indexer) {
                    $result = $indexer->reindex();
                    if($result === true) {
                        $sphinxIndexer = Mage::getModel('awadvancedsearch/engine_sphinx');
                        $result = $sphinxIndexer->reindex($indexer);
                        if($result) {
                            $index->setData('state', AW_Advancedsearch_Model_Source_Catalogindexes_State::READY)
                                  ->save();
                        } else {
                            self::_getLogHelper()->log(__CLASS__, 'Some error occurs on rebuilding index');
                        }
                    } else if($result === false) {
                        self::_getLogHelper()->log(__CLASS__, 'Some error occurs on rebuilding index');
                    } else {
                        self::_getLogHelper()->log(__CLASS__, $result);
                    }
                } else {
                    self::_getLogHelper()->log(__CLASS__, 'Invalid indexer');
                }
            }
            self::_getLogHelper()->log(__CLASS__, 'Done');
        }
    }

    protected static function _getLogHelper()
    {
        return Mage::helper('awadvancedsearch/log');
    }
}
