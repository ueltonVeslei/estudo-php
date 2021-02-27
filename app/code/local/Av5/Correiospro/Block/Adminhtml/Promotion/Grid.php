<?php
class Av5_Correiospro_Block_Adminhtml_Promotion_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct()
	{
		parent::__construct();
		$this->setId('promotions_grid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('av5_correiospro/promos')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
				'header'    => Mage::helper('av5_correiospro')->__('#'),
				'align'     =>'right',
				'width'     => '50px',
				'index'     => 'id',
		));
		
		$this->addColumn('nome', array(
				'header'    => Mage::helper('av5_correiospro')->__('Nome da Regra'),
				'align'     => 'left',
				'index'     => 'nome',
		));
		
		$this->addColumn('prioridade', array(
				'header'    => Mage::helper('av5_correiospro')->__('Prioridade'),
				'align'     => 'left',
				'index'     => 'prioridade',
		));
		
		$this->addColumn('status', array(
				'header'    => Mage::helper('av5_correiospro')->__('Status'),
				'align'     => 'center',
				'index'     => 'status',
				'type'		=> 'options',
				'options'	=> Mage::getModel('av5_correiospro/source_yesno')->toColumnOptionArray(),
		));
		
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}