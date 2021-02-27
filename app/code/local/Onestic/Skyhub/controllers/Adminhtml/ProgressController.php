<?php
class Onestic_Skyhub_Adminhtml_ProgressController extends Mage_Adminhtml_Controller_Action {
	
	public function invoiceAction() {
		if(!Mage::getModel('core/session')->getInvoiceTotal()) {
			Mage::getModel('core/session')->setInvoiceTotal(0);
		}
		$syncTotal = Mage::getModel('core/session')->getInvoiceTotal();
		$success = $errors = 0;
		$result = Mage::getModel('onestic_skyhub/updater')->syncInvoice();
		$total = $result['total'];
		$syncTotal += $result['count'];
		$success = $result['success'];
		$errors = $result['errors'];
		Mage::getModel('core/session')->setInvoiceTotal($syncTotal);
		
		if ($syncTotal >= $total) {
			$syncTotal = 0;
		}
		
		echo json_encode(array('count'=>$syncTotal,'success'=>$success,'errors'=>$errors,'total'=>$total));
	}
	
	public function shipmentAction() {
		if(!Mage::getModel('core/session')->getShipmentTotal()) {
			Mage::getModel('core/session')->setShipmentTotal(0);
		}
		$syncTotal = Mage::getModel('core/session')->getShipmentTotal();
		$success = $errors = 0;
		$result = Mage::getModel('onestic_skyhub/updater')->syncShipment();
		$total = $result['total'];
		$syncTotal += $result['count'];
		$success = $result['success'];
		$errors = $result['errors'];
		Mage::getModel('core/session')->setShipmentTotal($syncTotal);
	
		if ($syncTotal >= $total) {
			$syncTotal = 0;
		}
	
		echo json_encode(array('count'=>$syncTotal,'success'=>$success,'errors'=>$errors,'total'=>$total));
	}
	
	public function deliveryAction() {
		if(!Mage::getModel('core/session')->getDeliveryTotal()) {
			Mage::getModel('core/session')->setDeliveryTotal(0);
		}
		$syncTotal = Mage::getModel('core/session')->getDeliveryTotal();
		$success = $errors = 0;
		$result = Mage::getModel('onestic_skyhub/updater')->syncDelivery();
		$total = $result['total'];
		$syncTotal += $result['count'];
		$success = $result['success'];
		$errors = $result['errors'];
		Mage::getModel('core/session')->setDeliveryTotal($syncTotal);
	
		if ($syncTotal >= $total) {
			$syncTotal = 0;
		}
	
		echo json_encode(array('count'=>$syncTotal,'success'=>$success,'errors'=>$errors,'total'=>$total));
	}
	
	public function _isAllowed() {
	    return true;
	}
}