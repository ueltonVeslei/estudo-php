<?php
class Av5_LogViewer_Block_Adminhtml_Logs extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	public function __construct()
	{
		parent::__construct();
		$this->_controller = 'adminhtml_logs';
		$this->_blockGroup = 'logviewer';
		$this->_headerText = Mage::helper('logviewer')->__('Logs do Sistema');
		$this->_removeButton('add');
	}
	
}