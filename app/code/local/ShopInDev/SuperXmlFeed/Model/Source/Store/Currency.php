<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Source_Store_Currency {

	/**
	 * Options getter
	 * @return array
	 */
	public function toOptionArray(){

		$theOptions = Mage::app()->getStore()->getAvailableCurrencyCodes(true);
		$options = array();

		foreach( $theOptions as $value => $label ){

			$options[] = array(
				'value' => $label,
				'label' => Mage::app()->getLocale()
								 	  ->currency($label)
								 	  ->getName(). ' ('. $label. ')'
			);

		}

		return $options;
	}

	/**
	 * Get options in "key-value" format
	 * @return array
	 */
	public function toArray(){

		$data = $this->toOptionArray();
		$array = array();

		foreach( $data as $key => $value ){
			$array[ $value['value'] ] = $value['label'];
		}

		return $array;
	}

}