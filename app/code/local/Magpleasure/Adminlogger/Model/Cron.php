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
class Magpleasure_Adminlogger_Model_Cron
{
    const CACHE_LOCK_ID = 'admnlogger_cache_lock';

    const CRON_TIMEOUT = 3600;

    public static function run()
    {
        try {                          
            if(self::checkLock()){
                $keepDays = Mage::getStoreConfig('adminlogger/general/keep_days');
                if ($keepDays){

                    /** @var $log Magpleasure_Adminlogger_Model_Log */
                    $log = Mage::getResourceModel('adminlogger/log');
                    $log->clearLog($keepDays);

                    Mage::app()->removeCache(self::CACHE_LOCK_ID);
                }
            } else {
                echo "Admin Logger was locked";
            }
        } catch(Exception $e) {
            Mage::logException($e);
        }
    }

    public static function checkLock()
    {
        if($time = Mage::app()->loadCache(self::CACHE_LOCK_ID)){
            if((time() - $time) <= self::CRON_TIMEOUT){
                return false;
            }
        }
        Mage::app()->saveCache(time(), self::CACHE_LOCK_ID, array(), self::CRON_TIMEOUT);
        return true;
    }    
}