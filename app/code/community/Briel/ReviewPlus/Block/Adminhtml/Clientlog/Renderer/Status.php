<?php

class Briel_ReviewPlus_Block_Adminhtml_Clientlog_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
	public function render(Varien_Object $row) {
		$value =  $row->getData($this->getColumn()->getIndex());
		if ($value == 1) {
			return '<div style="text-align:center; font-weight:bold; color:green;">Sent</div>';
		} else {
			return '<div style="text-align:center; font-weight:bold; color:red;">Not sent</div>';
		}
	}
}
?>