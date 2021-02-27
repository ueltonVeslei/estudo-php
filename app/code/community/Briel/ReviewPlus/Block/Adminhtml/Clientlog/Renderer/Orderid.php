<?php

class Briel_ReviewPlus_Block_Adminhtml_Clientlog_Renderer_Orderid extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	public function render(Varien_Object $row) {
		$value =  $row->getData($this->getColumn()->getIndex());
		$order_db = Mage::getModel('sales/order')->load($value);
		return '<a style="color: ##ea7601;"  href="' . $this->getUrl('adminhtml/sales_order/view', array('order_id' => $value)) . '"><strong>' . $order_db->getIncrementId() . '</strong></a>';
	}
}
?>