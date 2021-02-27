<?php

/**
 * Adminhtml sales orders controller with order printing action
 *
 * @category    Nastnet
 * @package     Nastnet_PrintOrder
 * @author      Piotr Nastały <piotr.nastaly@nastnet.com>
 */
require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';
class Nastnet_OrderPrint_OrderController extends Mage_Adminhtml_Sales_OrderController
{

    
    /**
     * Print order
     * 
     */
	public function printAction(){
        $order = $this->_initOrder();
        if (!empty($order)) {
			$order->setOrder($order);
            $pdf = Mage::getModel('Nastnet_OrderPrint/order_pdf_order')->getPdf(array($order));
            return $this->_prepareDownloadResponse('order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
        }
        $this->_redirect('*/*/');
    }
    
}