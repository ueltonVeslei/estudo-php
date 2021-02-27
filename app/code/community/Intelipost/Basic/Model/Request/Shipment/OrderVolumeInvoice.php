<?php

class Intelipost_Basic_Model_Request_Shipment_OrderVolumeInvoice
// extends Varien_Object
{

    
    public $invoice_series;
    
    public $invoice_number;
   
    public $invoice_key;

    public $invoice_date;

    public $invoice_total_value;

    public $invoice_products_value;

    public $invoice_cfop;   

    /**
     * fetch collected data to quote     
     *
     * @param Intelipost_Shipping_Model_Carrier_Shipping_Data
     * @return Intelipost_Model_Request_Quote
     */    
    public function fetchRequest($order, $trackRequest, $volumes_qty)
    {    
        $invoice = $order->getInvoiceCollection();
        $invoiceData = $invoice->getData();
        $this->invoice_date = date('Y-m-d', strtotime($invoiceData['0']['created_at']));
        $total = $order->getBaseSubtotal() + $order->getShippingAmount();
        //$this->invoice_series = $trackRequest['invoice_series'];
        //$this->invoice_number = $trackRequest['invoice_number'];
        //$this->invoice_key = $trackRequest['invoice_key'];         
        $this->invoice_total_value = $total;//number_format((float)$total/$volumes_qty, 2, '.', '');
        $this->invoice_products_value = $order->getBaseSubtotal();//number_format((float)$order->getBaseSubtotal() / $volumes_qty, 2, '.', '');
        //$this->invoice_cfop = Mage::helper('quote')->getConfigData('invoice_cfop');

        $nfe = Mage::getModel('basic/nfes')->load($order->getIncrementId(),'increment_id');
        $this->invoice_series = $nfe->getSeries();
        $this->invoice_number = $nfe->getNumber();
        $this->invoice_key = $nfe->getKeyNfe();
        $this->invoice_cfop = $nfe->getCfop();

        return $this;

    }
} 

