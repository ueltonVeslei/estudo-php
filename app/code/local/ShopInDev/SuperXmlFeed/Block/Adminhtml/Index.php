<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Block_Adminhtml_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{

	/**
	 * Init container
	 * @return mixed
	 */
	public function __construct(){

		$this->_blockGroup = 'superxmlfeed';
		$this->_controller = 'adminhtml_index';
		$this->_headerText = Mage::helper('superxmlfeed')->__('XML Feeds');
		$this->_addButtonLabel = Mage::helper('superxmlfeed')->__('Add XML Feed');

		return parent::__construct();
	}

}
