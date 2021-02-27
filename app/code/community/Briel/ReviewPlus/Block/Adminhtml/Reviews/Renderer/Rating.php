<?php

class Briel_ReviewPlus_Block_Adminhtml_Reviews_Renderer_Rating extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
	public function render(Varien_Object $row) {
		$value =  (int)$row->getData($this->getColumn()->getIndex());
		if ($value == 1) {
			return '<strong style="color:#F89400;">'.$value.'</strong> star';
		} else {
			return '<strong style="color:#F89400;">'.$value.'</strong> stars';
		}
	}
}