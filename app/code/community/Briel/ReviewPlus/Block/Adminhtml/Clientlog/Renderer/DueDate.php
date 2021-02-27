<?php

class Briel_ReviewPlus_Block_Adminhtml_Clientlog_Renderer_DueDate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
	public function render(Varien_Object $row) {
		$value =  $row->getData($this->getColumn()->getIndex());
		return date('F j, Y', $value);
	}
}
?>