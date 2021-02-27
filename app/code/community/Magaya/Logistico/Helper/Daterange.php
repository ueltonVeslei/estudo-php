<?php

/**
 * Magaya
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@logistico.com so we can send you a copy immediately.
 *
 *
 * @category   Integration
 * @package    Magaya_Logistico
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magaya_Logistico_Helper_Daterange extends Mage_Core_Helper_Data
{
    public function getFromToDate($periodDates, $format=Varien_Date::DATETIME_INTERNAL_FORMAT, $toUtc=true)
    {
       $from = $to = "";
       $period = $periodDates ? $periodDates : '30d';
       $dates = $this->getDateRangeFromPeriodParam($period, $format, $toUtc);
       $from = $dates['from'];
       $to = $dates['to'];
       return array('from' => $from, 'to' => $to); 
    }
     
    protected function getDateRangeFromPeriodParam($period, $format=Varien_Date::DATETIME_INTERNAL_FORMAT, $toUtc=true)
    {
       $arr = $this->getDateRange($period, false, $toUtc);
       $arr['from'] =  $arr['from']->toString($format);
       $arr['to'] =  $arr['to']->toString($format);
       return $arr;
    }
    
    protected function getDateRange($range, $returnObjects = false, $toUTC=true)
    {
       $dateEnd   = Mage::app()->getLocale()->date();
       $dateStart = clone $dateEnd;
       $dateEnd->setHour(23);
       $dateEnd->setMinute(59);
       $dateEnd->setSecond(59);

       $dateStart->setHour(0);
       $dateStart->setMinute(0);
       $dateStart->setSecond(0);
       
       switch ($range) {
          case '7d':
               // substract 6 days we need to include
               // only today and not hte last one from range
               $dateStart->subDay(6);
              break;
          case '30d':
               $dateStart->subDay(30);
              break;

          case '90d':
               $dateStart->subDay(90);
              break;    
       }
        
       if ($toUTC) {
            $dateStart->setTimezone('Etc/UTC');
            $dateEnd->setTimezone('Etc/UTC');
       }

       if ($returnObjects) {
            return array($dateStart, $dateEnd);
       } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
       }
    }
}