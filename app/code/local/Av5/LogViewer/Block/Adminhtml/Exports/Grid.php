<?php
class Av5_LogViewer_Block_Adminhtml_Exports_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct()
	{
		parent::__construct();
		$this->setId('exports_grid');
		$this->setDefaultSort('time');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
		$this->setPagerVisibility(false);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('logviewer/exports');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('name', array(
				'header'    => Mage::helper('logviewer')->__('Nome do Arquivo'),
				'align'     => 'left',
				'index'     => 'name',
		));
		
		$this->addColumn('date_object', array(
				'header'    => Mage::helper('logviewer')->__('Data de modificação'),
				'align'     => 'center',
				'index'     => 'date_object',
				'type'		=> 'date'
		));
		
		$this->addColumn('size', array(
				'header'    => Mage::helper('logviewer')->__('Tamanho (Bytes)'),
				'sortable' => false,
				'filter'   => false,
				'align'     => 'center',
				'index'     => 'size',
		));
		
		$this->addColumn('action', array(
				'header'   => $this->helper('logviewer')->__('Ações'),
				'sortable' => false,
				'filter'   => false,
				'type'     => 'action',
				'getter'   => 'getName',
				'actions'  => array(
						array(
								'caption'     => $this->helper('logviewer')->__('Download'),
								'url'         => array('base'=> '*/*/download'),
								'field'       => 'name'
						),
				)
		));
		
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/view', array('name' => $row->getName()));
	}
}