<?php
class Av5_Correiospro_Block_Adminhtml_Promotion extends Mage_Adminhtml_Block_Widget_Grid_Container {
	protected $_addButtonLabel = 'Nova Regra';
	
	public function __construct()
	{
		parent::__construct();
		$this->_controller = 'adminhtml_promotion';
		$this->_blockGroup = 'av5_correiospro';
		$this->_headerText = Mage::helper('av5_correiospro')->__('Regras Correios');
	}
}