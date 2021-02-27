<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Block_Adminhtml_Index_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

	/**
	 * Render override
	 * @param object $row
	 * @return mixed
	 */
	public function render(Varien_Object $row){

		$this->getColumn()->setActions(array(array(
			'url' => $this->getUrl('*/*/generate', array('xml_id' => $row->getXmlId())),
			'caption' => Mage::helper('superxmlfeed')->__('Generate'),
		)));

		return parent::render($row);
	}

}
