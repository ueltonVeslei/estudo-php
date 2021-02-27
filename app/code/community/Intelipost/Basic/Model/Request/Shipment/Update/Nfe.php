<?php

class Intelipost_Basic_Model_Request_Shipment_Update_Nfe
// extends Varien_Object
{
	public $order_number;
	public $shipment_order_volume_invoice_array = array();

	public function fetchRequest($order, $nfe_data)
	{
		$this->order_number = $order->getIncrementId();
		
		$invoice = $order->getInvoiceCollection();
        $invoiceData = $invoice->getData();
        $invoice_date = date('Y-m-d', strtotime($invoiceData['0']['created_at']));

		$dimension = Mage::getModel('basic/package_dimension');
        $dimension->calcItemsDimension($order->getAllItems());
        $i = 0;

        foreach ($dimension->getPackages () as $id => $box)
        {
			$nfe = array( 	'shipment_order_volume_number' => ++$i ,
							'invoice_series' => $nfe_data['series'],
							'invoice_number' => $nfe_data['number'],
							'invoice_key'    => $nfe_data['key_nfe'],
							'invoice_date'   => $invoice_date,
							'invoice_total_value' => $order->getGrandTotal(),
							'invoice_products_value' => $order->getBaseSubtotal(),
							'invoice_cfop' => $nfe_data['cfop']);

			array_push($this->shipment_order_volume_invoice_array, $nfe);
		}

		return $this;
	}
}