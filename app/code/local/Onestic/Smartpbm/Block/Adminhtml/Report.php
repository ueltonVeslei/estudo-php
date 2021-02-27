<?php
class Onestic_Smartpbm_Block_Adminhtml_Report extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	public function __construct()
	{
		parent::__construct();
		$this->_controller = 'adminhtml_report';
		$this->_blockGroup = 'smartpbm';
		$this->_headerText = Mage::helper('smartpbm')->__('Relatório');
		$this->_removeButton('add');
	}
}