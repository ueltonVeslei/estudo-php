<?php
class Av5_LogViewer_Block_Adminhtml_Report extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	public function __construct()
	{
		parent::__construct();
		$this->_controller = 'adminhtml_report';
		$this->_blockGroup = 'logviewer';
		$this->_headerText = Mage::helper('logviewer')->__('Reports do Sistema');
		$this->_removeButton('add');
	}
	
}