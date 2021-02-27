<?php


class Intelipost_Push_Block_Adminhtml_Orders_Romaneio extends Mage_Adminhtml_Block_Template
{
	protected $correios_methods = array('pac' => 1, 'sedex' => 2, 'esedex' => 3);
	protected $methodId;
	public function __construct ()
	{
		parent::__construct ();
		
		$this->setTemplate ('intelipost/push/report.phtml');
	}

	public function getOrderIds()
	{
		return $this->getOrdersIds();
	}

	public function getMethodName()
	{
		return $this->getShippingMethodName();
	}

	public function isCorreios()
	{
		$this->methodId = $this->getMethodId();
		if (in_array($this->methodId, $this->correios_methods)) {
			return true;
		}

		return false;
	}

	public function getMethodId()
	{
		foreach ($this->getOrderIds() as $order) 
		{			
			$intelipost_order = Mage::getModel('basic/orders')->load($order->order_id, 'order_id');
			return $intelipost_order->getDeliveryMethodId();
		}
	}

	public function getCorreiosConfig($configName)
	{
		switch ($this->methodId) 
		{
			case 1:
				return Mage::getStoreConfig ('intelipost_push/romaneio_pac_info/' . $configName);
				break;			
			case 2:
				return Mage::getStoreConfig ('intelipost_push/romaneio_sedex_info/'. $configName);
				break;
			case 3:
				return Mage::getStoreConfig ('intelipost_push/romaneio_esedex_info/' .$configName);
				break;
		}
	}

	public function getCountOrders()
	{
		return count($this->getOrderIds());
	}

	public function getInvoiceNumberData($incrementId)
	{
		$retorno = '-';
		$intelipost_nfes = Mage::getModel("basic/nfes")->load($incrementId, 'increment_id');

		if (count($intelipost_nfes->getData()) > 0)
		{
			$retorno = $intelipost_nfes->getNumber();
		}

		return $retorno;
	}

	public function getOrderData($order_id)
	{
		$order = Mage::getModel('sales/order')->load($order_id);
		$this->setOrderIncrementId($order->getIncrementId());		
		$target = $this->getTrackingCodeData($order->getIncrementId());
		$target = $target == '-' ? $this->getInvoiceNumberData($order->getIncrementId()) : $target;
		$this->setTrackingInvoice($target);
		$this->setPostCode($order->getShippingAddress()->getPostcode());
		$this->setUf($order->getShippingAddress()->getRegionCode());
	}

	public function getTrackingCodeData($incrementId)
	{
		$retorno = '-';
		$intelipost_tracking = Mage::getModel("basic/trackings")->load($incrementId, 'increment_id');

		if (count($intelipost_tracking->getData()) > 0)
		{
			$retorno = $intelipost_tracking->getCode();
		}

		return $retorno;
	}
}