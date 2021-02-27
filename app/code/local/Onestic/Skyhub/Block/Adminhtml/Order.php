<?php
class Onestic_Skyhub_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container {
		
	public function __construct()
	{
		parent::__construct();
		$this->_controller = 'adminhtml_order';
		$this->_blockGroup = 'onestic_skyhub';
		$this->_headerText = Mage::helper('onestic_skyhub')->__('Pedidos Skyhub');
		$this->_removeButton('add');
		
		$data = array(
		    'label'     => 'Recuperar Pedidos',
		    'class'     => 'some-class',
		    'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/last') . '\')',
		);
		$this->addButton('btn_populate', $data);
		
		$data = array(
				'label'     => 'Importar Pedidos',
				'class'     => 'some-class',
				'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/import') . '\')',
		);
		$this->addButton('btn_import', $data);
		
		$data = array(
				'label'     => 'Sincronizar Tudo',
				'class'     => 'some-class',
				'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/resyncprogress') . '\')',
		);
		$this->addButton('btn_resync', $data);
		
		$data = array(
				'label'     => 'Sincronizar NFs',
				'class'     => 'some-class',
				'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/invoice') . '\')',
		);
		$this->addButton('btn_sync_invoice', $data);
		
		$data = array(
				'label'     => 'Sincronizar Envios',
				'class'     => 'some-class',
				'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/shipment') . '\')',
		);
		$this->addButton('btn_sync_shipment', $data);
		
		$data = array(
				'label'     => 'Sincronizar Entregas',
				'class'     => 'some-class',
				'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/delivered') . '\')',
		);
		$this->addButton('btn_sync_delivered', $data);
	}
}