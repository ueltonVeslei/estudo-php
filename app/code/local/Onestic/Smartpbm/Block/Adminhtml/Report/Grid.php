<?php
class Onestic_Smartpbm_Block_Adminhtml_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct()
	{
		parent::__construct();
		$this->setId('report_grid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('smartpbm/order')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('order_increment_id', array(
				'header'    => Mage::helper('smartpbm')->__('Nro. Pedido'),
				'align'     => 'left',
				'index'     => 'order_increment_id',
		));
		
		$this->addColumn('pbm', array(
				'header'    => Mage::helper('smartpbm')->__('PBM'),
				'align'     =>'left',
				'index'     => 'pbm',
				'type'		=> 'options',
				'options'	=> Mage::getModel('smartpbm/source_pbms')->toColumnOptionArray()
		));
	
		$this->addColumn('ean', array(
				'header'    => Mage::helper('smartpbm')->__('EAN'),
				'align'     => 'center',
				'index'     => 'ean',
		));
		
		$this->addColumn('product_name', array(
		    'header'    => Mage::helper('smartpbm')->__('Produto'),
		    'align'     => 'center',
		    'index'     => 'product_name',
		));
		
		$this->addColumn('discount', array(
				'header'    => Mage::helper('smartpbm')->__('% Desconto'),
				'align'     => 'center',
				'index'     => 'discount',
		));
		
		$this->addColumn('original_price', array(
		    'header'    => Mage::helper('smartpbm')->__('Valor Original'),
		    'align'     => 'center',
		    'index'     => 'original_price',
		));
		
		$this->addColumn('finalprice', array(
		    'header'    => Mage::helper('smartpbm')->__('Preço Final'),
		    'align'     => 'center',
		    'index'     => 'finalprice',
		));
		
		return parent::_prepareColumns();
	}
	
}