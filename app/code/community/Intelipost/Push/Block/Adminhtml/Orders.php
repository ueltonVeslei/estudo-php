<?php


class Intelipost_Push_Block_Adminhtml_Orders extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_orders";
	$this->_blockGroup = "push";
	$this->_headerText = Mage::helper("push")->__("Orders Manager");
	// $this->_addButtonLabel = Mage::helper("push")->__("Add New Item");
	parent::__construct();
	
	$this->_removeButton ('add');
	
	}

}

