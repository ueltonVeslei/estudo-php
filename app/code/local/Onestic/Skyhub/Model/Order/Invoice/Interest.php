<?php
class Onestic_Skyhub_Model_Order_Invoice_Interest extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
	{
		$order = $invoice->getOrder();
        
        $invoice->setGrandTotal($invoice->getGrandTotal() + $order->getInterest());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $order->getBaseInterest());
		
		return $this;
	}
}
