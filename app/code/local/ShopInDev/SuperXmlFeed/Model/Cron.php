<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Cron{

	const XML_PATH_GENERATION_ENABLED = 'superxmlfeed/generate/enabled';

	/**
	 * Generate xml feeds
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @return void
	 */
	public function scheduledGenerateXmls($schedule){

		if( !Mage::getStoreConfigFlag(self::XML_PATH_GENERATION_ENABLED) ){
			return;
		}

		$errors = array();
		$collection = Mage::getModel('superxmlfeed/xml')->getCollection();

		foreach( $collection as $xml ){
			try {
				$xml->generateXml();
			}
			catch (Exception $e) {
				$errors[] = $e->getMessage();
			}
		}

	}

}