<?php
class Onestic_Skyhub_Model_Order_Creditmemo_Interest extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
	{
		$order = $creditmemo->getOrder();
        
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $order->getInterest());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $order->getBaseInterest());
		
		return $this;
	}
}