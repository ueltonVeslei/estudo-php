<?php

class Briel_ReviewPlus_Block_Adminhtml_Reviews_Renderer_EditAction extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
	public function render(Varien_Object $row) {
		$value =  (int)$row->getData($this->getColumn()->getIndex());
		$reviewplus_db = Mage::getModel('reviewplus/reviews')->load($value);
		$status = $reviewplus_db->getData('review_status');
		if ($status == 'pending') {
			return '<a href="' . $this->getUrl('adminhtml/reviews/edit', array('id' => $value)) . '">' . '<strong>Edit</strong>' . '</a>';
		} else {
			return "-";
		}
	}
}
?>