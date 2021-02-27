<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Source_Cron_Frequency {

    const CRON_MINUTE = 'MM';
    const CRON_HOUR   = 'H';
    const CRON_DAY    = 'D';
    const CRON_WEEK   = 'W';
    const CRON_MONTH  = 'M';

    /**
     * Retrieve source options
     * @return array
     */
    public function toOptionArray(){

        $options = array(
            array(
                'label' => Mage::helper('superxmlfeed')->__('Each 30 minutes'),
                'value' => '30_'. self::CRON_MINUTE,
            ),
            array(
                'label' => Mage::helper('superxmlfeed')->__('Each 1 hour'),
                'value' => '1_'. self::CRON_HOUR,
            ),
            array(
                'label' => Mage::helper('superxmlfeed')->__('Each 2 hours'),
                'value' => '2_'. self::CRON_HOUR,
            ),
            array(
                'label' => Mage::helper('superxmlfeed')->__('Each 4 hours'),
                'value' => '4_'. self::CRON_HOUR,
            ),
            array(
                'label' => Mage::helper('superxmlfeed')->__('Each 6 hours'),
                'value' => '6_'. self::CRON_HOUR,
            ),
            array(
                'label' => Mage::helper('superxmlfeed')->__('Each 8 hours'),
                'value' => '8_'. self::CRON_HOUR,
            ),
            array(
                'label' => Mage::helper('superxmlfeed')->__('Daily'),
                'value' => '1_'. self::CRON_DAY,
            ),
            array(
                'label' => Mage::helper('superxmlfeed')->__('Weekly'),
                'value' => '1_'. self::CRON_WEEK,
            ),
            array(
                'label' => Mage::helper('superxmlfeed')->__('Monthly'),
                'value' => '1_'. self::CRON_MONTH,
            )
        );

        return $options;
    }

}