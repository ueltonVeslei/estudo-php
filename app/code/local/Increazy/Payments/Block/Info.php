<?php
class Increazy_Payments_Block_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('increazy/payments/info.phtml');
    }

	public function getOrder()
    {
        $order = Mage::registry('current_order');
		$info = $this->getInfo();
		if (!$order) {
			if ($this->getInfo() instanceof Mage_Sales_Model_Order_Payment) {
				$order = $this->getInfo()->getOrder();
			}
		}
		return($order);
    }
}