<?php
class Onestic_Skyhub_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Widget_Grid_Container {
		
	public function __construct()
	{
		parent::__construct();
		$this->_controller = 'adminhtml_product';
		$this->_blockGroup = 'onestic_skyhub';
		$this->_headerText = Mage::helper('onestic_skyhub')->__('Produtos Skyhub');
		$this->_removeButton('add');
		
		$data = array(
		    'label'     => 'Recuperar Produtos',
		    'class'     => 'some-class',
		    'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/progress') . '\')',
		);
		$this->addButton('btn_populate', $data);
		
		$data = array(
				'label'     => 'Exportar Produtos',
				'class'     => 'some-class',
				'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/export') . '\')',
		);
		$this->addButton('btn_export', $data);
		
		$data = array(
				'label'     => 'Sincronizar Produtos',
				'class'     => 'some-class',
				'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/resyncprogress') . '\')',
		);
		$this->addButton('btn_resync', $data);
		
		$data = array(
				'label'     => 'Limpar Tabela',
				'class'     => 'some-class',
				'onclick'   => 'setLocation(\''  . $this->getUrl('*/*/clean') . '\')',
		);
		$this->addButton('btn_clean', $data);
	}
}