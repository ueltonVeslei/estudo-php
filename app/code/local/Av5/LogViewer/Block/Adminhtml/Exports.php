<?php
class Av5_LogViewer_Block_Adminhtml_Exports extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	public function __construct()
	{
		parent::__construct();
		$this->_controller = 'adminhtml_exports';
		$this->_blockGroup = 'logviewer';
		$this->_headerText = Mage::helper('logviewer')->__('Exportações do Sistema');
		$this->_removeButton('add');
	}
	
}