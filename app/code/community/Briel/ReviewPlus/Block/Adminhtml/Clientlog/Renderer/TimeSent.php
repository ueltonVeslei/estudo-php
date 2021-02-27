<?php

class Briel_ReviewPlus_Block_Adminhtml_Clientlog_Renderer_TimeSent extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
	public function render(Varien_Object $row) {
		$value =  $row->getData($this->getColumn()->getIndex());
		if ($value == 0) {
			return '<strong>-</strong>';
		} else {
			return date('F j, Y - H:i', $value);
		}
	}
}
?>