<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Config_Backend_Cron extends Mage_Core_Model_Config_Data {

	const CRON_STRING_PATH = 'crontab/jobs/superxmlfeed_generate/schedule/cron_expr';
	const CRON_MODEL_PATH = 'crontab/jobs/superxmlfeed_generate/run/model';

	/**
	 * After save override
	 * @return void
	 */
	protected function _afterSave(){

		$enabled = $this->getData('groups/generate/fields/enabled/value');
		$time = $this->getData('groups/generate/fields/time/value');
		$frequency = $this->getData('groups/generate/fields/frequency/value');
		
		$frequency = explode('_', $frequency);
		$step = $frequency[0];
		$type = $frequency[1];

		$typeMinute = ShopInDev_SuperXmlFeed_Model_Source_Cron_Frequency::CRON_MINUTE;
		$typeHour = ShopInDev_SuperXmlFeed_Model_Source_Cron_Frequency::CRON_HOUR;
		$typeDay = ShopInDev_SuperXmlFeed_Model_Source_Cron_Frequency::CRON_DAY;
		$typeWeek = ShopInDev_SuperXmlFeed_Model_Source_Cron_Frequency::CRON_WEEK;
		$typeMonth = ShopInDev_SuperXmlFeed_Model_Source_Cron_Frequency::CRON_MONTH;

		if( $enabled ){
			
			$minute = intval($time[1]);
			$hour = intval($time[0]);
			$day = ($type == $typeMonth) ? '1' : '*';
			$month = '*';
			$dayWeek = ($type == $typeWeek) ? '1' : '*';

			// Minutes
			if( $type == $typeMinute AND $step == 30 ){
				$minute = '0,30';
				$hour = '*';
			}

			// Hours
			if( $type == $typeHour ){
				$minute = '0';
				$hour = '*/'. $step;
			}

			$cronExprArray = array(
				$minute, # Minute
				$hour,   # Hour
				$day,    # Day of the Month
				$month,  # Month of the Year
				$dayWeek # Day of the Week
			);

			$cronExprString = join(' ', $cronExprArray);

		}else{
			$cronExprString = '';
		}

		try {

			Mage::getModel('core/config_data')
				->load(self::CRON_STRING_PATH, 'path')
				->setValue($cronExprString)
				->setPath(self::CRON_STRING_PATH)
				->save();

		} catch (Exception $e) {
			throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
		}

	}

}
