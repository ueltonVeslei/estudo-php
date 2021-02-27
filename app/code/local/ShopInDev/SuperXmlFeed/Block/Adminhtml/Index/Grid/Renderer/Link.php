<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Block_Adminhtml_Index_Grid_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	/**
	 * Render override
	 * @param object $row
	 * @return mixed
	 */
	public function render(Varien_Object $row){

		$fileName = preg_replace('/^\//', '', $row->getXmlPath(). $row->getXmlFilename());
		$url = $this->escapeHtml(
			Mage::app()->getStore($row->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). $fileName
		);

		if( file_exists(BP. DS. $fileName) ){
			return sprintf('<a href="%1$s">%1$s</a>', $url);
		}

		return $url;
	}

}
