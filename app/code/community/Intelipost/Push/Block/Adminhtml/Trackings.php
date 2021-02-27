<?php

class Intelipost_Push_Block_Adminhtml_Trackings extends Mage_Adminhtml_Block_Widget_Grid_Container
{

public function __construct()
{
	$this->_controller = "adminhtml_trackings";
	$this->_blockGroup = "push";
	$this->_headerText = Mage::helper("push")->__("Trackings Manager");
	$this->_addButtonLabel = Mage::helper("push")->__("Add");
	
	parent::__construct();
	
	// $this->_removeButton ('add');
}

}

