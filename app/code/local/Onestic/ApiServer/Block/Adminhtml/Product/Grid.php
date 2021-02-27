<?php
class Onestic_ApiServer_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct()
	{
		parent::__construct();
		$this->setId('product_grid');
		$this->setDefaultSort('updated_at');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('onestic_apiserver/products')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('sku', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Sku Produto'),
				'align'     =>'left',
				'index'     => 'sku',
		));
		
		$this->addColumn('name', array(
		    'header'    => Mage::helper('onestic_apiserver')->__('Nome'),
		    'align'     =>'left',
		    'index'     => 'name',
		));
		
		$this->addColumn('update_at', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Última Atualização'),
				'type'		=> 'date',
				'align'     =>'center',
				'index'     => 'updated_at',
		));
		
		$this->addColumn('qty', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Estoque'),
				'align'     =>'center',
				'index'     => 'qty',
		));
		
		$this->addColumn('price', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Preço'),
				'type'		=> 'currency',
				'align'     =>'center',
				'index'     => 'price',
		));
		
		$this->addColumn('promotional_price', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Preço Promo'),
				'type'		=> 'currency',
				'align'     =>'center',
				'index'     => 'promotional_price',
		));
		
		$this->addColumn('status', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Status'),
				'align'     =>'center',
				'index'     => 'status',
				'type'		=> 'options',
				'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_StatusProduct')->toColumnOptionArray(),
		));
	
		$this->addColumn('removed', array(
		    'header'    => Mage::helper('onestic_apiserver')->__('Removido Roche'),
		    'align'     =>'center',
		    'index'     => 'removed',
		    'type'		=> 'options',
		    'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_SimNao')->toColumnOptionArray(),
			'renderer'	=> new Onestic_ApiServer_Block_Adminhtml_Product_Grid_Renderer_Simnao(),
		));
		
		$this->addColumn('status_sync', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Sincronizado?'),
				'align'     =>'center',
				'index'     => 'status_sync',
				'type'		=> 'options',
				'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_SimNao')->toColumnOptionArray(),
				'renderer'	=> new Onestic_ApiServer_Block_Adminhtml_Product_Grid_Renderer_Simnao(),
		));
		
		$this->addColumn('action', array(
		    'header'   => $this->helper('catalog')->__('Ações'),
		    'sortable' => false,
		    'filter'   => false,
		    'type'     => 'action',
		    'getter'   => 'getId',
		    'actions'  => array(
		        array(
		            'caption'     => $this->helper('onestic_apiserver')->__('Sincronizar'),
		            'url'         => array('base'=> '*/*/sync'),
                    'field'       => 'id'
		        ),
	    		//array(
    			//	'caption'     => $this->helper('onestic_apiserver')->__('Excluir'),
    			//	'url'         => array('base'=> '*/*/delete'),
    			//	'field'       => 'id'
	    		//),
		    )
		));
	
		return parent::_prepareColumns();
	}
	
}