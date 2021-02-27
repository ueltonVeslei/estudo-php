<?php
/**
 * ShopInDev
 *
 * @category    ShopInDev
 * @package     ShopInDev_SuperXmlFeed
 * @copyright   Copyright (c) 2016 ShopInDev
 * @license     http://opensource.org/licenses/GPL-3.0 GNU General Public License (GPL)
 */

class ShopInDev_SuperXmlFeed_Block_Adminhtml_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	/**
	 * Init container
	 * @return void
	 */
	public function __construct(){

		$this->_blockGroup = 'superxmlfeed';
		$this->_controller = 'adminhtml_index';

		parent::__construct();

		$this->setId('superxmlfeedGrid');
		$this->setDefaultSort('xml_id');

	}

	/**
	 * Prepare collection override
	 * @return mixed
	 */
	protected function _prepareCollection(){

		$collection = Mage::getModel('superxmlfeed/xml')->getCollection();
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	/**
	 * Prepare columns override
	 * @return mixed
	 */
	protected function _prepareColumns(){

		$this->addColumn('xml_id', array(
			'header'    => Mage::helper('superxmlfeed')->__('ID'),
			'width'     => '50px',
			'index'     => 'xml_id'
		));

		$this->addColumn('xml_filename', array(
			'header'    => Mage::helper('superxmlfeed')->__('Filename'),
			'index'     => 'xml_filename'
		));

		$this->addColumn('xml_path', array(
			'header'    => Mage::helper('superxmlfeed')->__('Path'),
			'index'     => 'xml_path'
		));

		$this->addColumn('link', array(
			'header'    => Mage::helper('superxmlfeed')->__('Link'),
			'index'     => 'concat(xml_path, xml_filename)',
			'renderer'  => 'superxmlfeed/adminhtml_index_grid_renderer_link',
		));

		$this->addColumn('xml_time', array(
			'header'    => Mage::helper('superxmlfeed')->__('Last Time Generated'),
			'width'     => '150px',
			'index'     => 'xml_time',
			'type'      => 'datetime',
		));

		if( !Mage::app()->isSingleStoreMode() ){
			$this->addColumn('store_id', array(
				'header'    => Mage::helper('superxmlfeed')->__('Store View'),
				'index'     => 'store_id',
				'type'      => 'store',
			));
		}

		$currencies = Mage::app()->getStore()->getAvailableCurrencyCodes(true);

		if( is_array($currencies) AND count($currencies) > 1 ){
			$this->addColumn('store_currency', array(
				'header'    => Mage::helper('superxmlfeed')->__('Store Currency'),
				'index'     => 'store_currency',
			));
		}

		$this->addColumn('action', array(
			'header'   => Mage::helper('superxmlfeed')->__('Action'),
			'filter'   => false,
			'sortable' => false,
			'width'    => '100',
			'renderer' => 'superxmlfeed/adminhtml_index_grid_renderer_action'
		));

		return parent::_prepareColumns();
	}

	/**
	 * Row click url
	 * @return string
	 */
	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('xml_id' => $row->getId()));
	}

}
