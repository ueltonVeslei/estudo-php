<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Model_Source_Product_Type {

	/**
	 * Options getter
	 * @return array
	 */
	public function toOptionArray(){

		$theOptions = Mage_Catalog_Model_Product_Type::getOptionArray();
		$options = array();

		foreach( $theOptions as $value => $label ){
			$options[] = array(
				'label' => $label,
				'value' => $value
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