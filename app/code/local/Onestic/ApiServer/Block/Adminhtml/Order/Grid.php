<?php
class Onestic_ApiServer_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct()
	{
		parent::__construct();
		$this->setId('orders_grid');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel('onestic_apiserver/orders')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('code', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Nro Pedido Roche'),
				'align'     =>'left',
				'index'     => 'code',
		));
		
		$this->addColumn('increment_id', array(
		    'header'    => Mage::helper('onestic_apiserver')->__('Nro Pedido Magento'),
		    'align'     =>'left',
		    'index'     => 'increment_id',
		));
		
		$this->addColumn('created_at', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Data'),
				'type'		=> 'date',
				'align'     =>'center',
				'index'     => 'created_at',
		));
		
		$this->addColumn('name', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Cliente'),
				'align'     => 'left',
				'index'     => 'name',
		));
		
		$this->addColumn('status_apiserver', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Status'),
				'align'     =>'center',
				'index'     => 'status_apiserver',
				'type'		=> 'options',
				'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_Status')->toColumnOptionArray(),
		));
	
		$this->addColumn('status_sync', array(
				'header'    => Mage::helper('onestic_apiserver')->__('Enviado na Roche?'),
				'align'     =>'center',
				'index'     => 'status_sync',
				'type'		=> 'options',
				'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_Sync')->toColumnOptionArray(),
				'renderer'	=> new Onestic_ApiServer_Block_Adminhtml_Order_Grid_Renderer_Simnao(),
		));
	
		$this->addColumn('status_invoice_mg', array(
		    'header'    => Mage::helper('onestic_apiserver')->__('NF Magento'),
		    'align'     =>'center',
		    'index'     => 'status_invoice_mg',
		    'type'		=> 'options',
		    'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_SimNao')->toColumnOptionArray(),
			'renderer'	=> new Onestic_ApiServer_Block_Adminhtml_Order_Grid_Renderer_Simnao(),
		));
		
		$this->addColumn('status_invoice_sh', array(
		    'header'    => Mage::helper('onestic_apiserver')->__('NF Roche'),
		    'align'     =>'center',
		    'index'     => 'status_invoice_sh',
		    'type'		=> 'options',
		    'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_SimNao')->toColumnOptionArray(),
			'renderer'	=> new Onestic_ApiServer_Block_Adminhtml_Order_Grid_Renderer_Simnao(),
		));
		
		$this->addColumn('status_shipment_mg', array(
		    'header'    => Mage::helper('onestic_apiserver')->__('Envio Magento'),
		    'align'     =>'center',
		    'index'     => 'status_shipment_mg',
		    'type'		=> 'options',
		    'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_SimNao')->toColumnOptionArray(),
			'renderer'	=> new Onestic_ApiServer_Block_Adminhtml_Order_Grid_Renderer_Simnao(),
		));
		
		$this->addColumn('status_shipment_sh', array(
		    'header'    => Mage::helper('onestic_apiserver')->__('Envio Roche'),
		    'align'     =>'center',
		    'index'     => 'status_shipment_sh',
		    'type'		=> 'options',
		    'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_SimNao')->toColumnOptionArray(),
			'renderer'	=> new Onestic_ApiServer_Block_Adminhtml_Order_Grid_Renderer_Simnao(),
		));
		
		$this->addColumn('status_delivery_mg', array(
		    'header'    => Mage::helper('onestic_apiserver')->__('Entregue Magento'),
		    'align'     =>'center',
		    'index'     => 'status_delivery_mg',
		    'type'		=> 'options',
		    'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_SimNao')->toColumnOptionArray(),
			'renderer'	=> new Onestic_ApiServer_Block_Adminhtml_Order_Grid_Renderer_Simnao(),
		));
		
		$this->addColumn('status_delivery_sh', array(
		    'header'    => Mage::helper('onestic_apiserver')->__('Entregue Roche'),
		    'align'     =>'center',
		    'index'     => 'status_delivery_sh',
		    'type'		=> 'options',
		    'options'	=> Mage::getModel('Onestic_ApiServer_Model_Source_SimNao')->toColumnOptionArray(),
			'renderer'	=> new Onestic_ApiServer_Block_Adminhtml_Order_Grid_Renderer_Simnao(),
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