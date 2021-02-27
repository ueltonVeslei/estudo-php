<?php

class Intelipost_Basic_Model_Request_Shipment_Order
// extends Varien_Object
{

    public $order_number;

    public $created;

    public $shipped_date;

    public $estimated_delivery_date;
       
    public $end_customer;

    public $shipment_order_volume_array = array();

    public $provider_shipping_cost;

    public $customer_shipping_costs;

    public $logistic_provider;
   
    public $delivery_method_description;

    public $quote_id;

    public $delivery_method_id;

    public $origin_warehouse_code;

    /**
     * fetch collected data to quote     
     *
     * @param Intelipost_Shipping_Model_Carrier_Shipping_Data
     * @return Intelipost_Model_Request_Quote
     */    
    public function fetchTrackRequest($order, $trackRequest)
    {           
        $this->end_customer = Mage::getModel('basic/request_customer');
        $this->end_customer->fetchRequest($order, $trackRequest);        
        
        if (Mage::getStoreConfig('intelipost_push/general/send_order_date'))
        {
            $this->created = str_replace(' ', 'T', $this->getInvoiceDate($order));
        }

        if (Mage::getStoreConfig('intelipost_push/general/shipped_on_create'))
        {
            $this->shipped_date = (str_replace(' ', 'T', $this->getInvoiceDate($order)));
        }

        $calcMode       = Mage::getStoreConfig ('intelipost_basic/settings/quote_method');
        $calcDimensions = Mage::getStoreConfig ('intelipost_basic/quote_volume/advanced_vol_calc');
        if ($calcDimensions != 'no' || $calcMode == 'product')
        {
            $dimension = Mage::getModel('basic/package_dimension');
            $dimension->calcItemsDimension($order->getAllItems());
            $i = 0;            

            $packages = Mage::helper('basic')->checkOrderQtyVolumes($dimension->getPackages(), $order->getId());        
            $qty_packages = count($packages);

            foreach ($packages as $id => $box)
            {
                $current_index = ++$i;

                $orderVolume = Mage::getModel('basic/request_shipment_orderVolumeArray');
                $orderVolume->shipment_order_volume_number = $current_index;
                $orderVolume->fetchRequest($order, $trackRequest, $qty_packages);

                $orderVolume->width  = $box ['width'];
                $orderVolume->height = $box ['height'];
                $orderVolume->length = $box ['length'];
                $orderVolume->weight = $box ['weight'];
                $orderVolume->products_quantity = $box ['qty'];

               // $this->estimated_delivery_date = Mage::helper('basic')->getEstimatedDeliveryDate($order->getShippingDescription(), $orderVolume->shipment_order_volume_invoice->invoice_date);
                array_push($this->shipment_order_volume_array, $orderVolume);
            }
        }
        else
        {
            $orderVolume = Mage::getModel('basic/request_shipment_orderVolumeArray');
            $orderVolume->fetchRequest($order, $trackRequest, $qty_packages);

            //$this->estimated_delivery_date = Mage::helper('basic')->getEstimatedDeliveryDate($order->getShippingDescription(), $orderVolume->shipment_order_volume_invoice->invoice_date);
            array_push($this->shipment_order_volume_array, $orderVolume);
        }

        $this->order_number = $order->getIncrementId();

        //$this->provider_shipping_cost = $order->getShippingAmount();
        $this->customer_shipping_costs = $order->getShippingAmount();
        
        $this->logistic_provider = Mage::helper('basic')->getLogisticProvider($order->getShippingMethod());
        $this->delivery_method_description = Mage::helper('basic')->getShippingMethod($order->getShippingMethod());
        
        $basic_order = Mage::getModel('basic/orders')->load($order->getId(),'order_id');
        if(!empty($basic_order) && $basic_order->getId()>0)
        {
            if ($this->logistic_provider == $basic_order->getDeliveryMethodId()) {
                $this->quote_id = $basic_order->getDeliveryQuoteId();
            }
            
            $this->delivery_method_id = $basic_order->getDeliveryMethodId();
            $this->provider_shipping_cost = $basic_order->getShippingCost();
        }

        $invoice = $order->getInvoiceCollection();
        $invoiceData = $invoice->getData();
        if (isset($invoiceData['0']['created_at']))
        {
            $invoice_date = date('Y-m-d', strtotime($invoiceData['0']['created_at']));
        }
        else
        {
            $invoice_date = date('Y-m-d');
        }
        
        $this->estimated_delivery_date = Mage::helper('basic')->getEstimatedDeliveryDate($order->getShippingDescription(), $invoice_date, $order->getId());
        // var_dump ($this); // die;

        $this->origin_warehouse_code = $this->getWarehouse($order->getShippingAddress()->getPostcode());
        return $this;

    }

    public function getInvoiceDate($order)
    {
        $invoice = $order->getInvoiceCollection();
        $invoiceData = $invoice->getData();
        $invoice_date = date('Y-m-d H:i:s', strtotime($invoiceData['0']['created_at']));

        return $invoice_date;
    }

    public function getWarehouse($dest_postcode)
    {
        if (Mage::getStoreConfig('intelipost_basic/settings/use_another_origin'))
        {
            $dest_postcode = (int)$this->removePostcodeFormat($dest_postcode);
            $min_range = (int)$this->removePostcodeFormat(Mage::getStoreConfig('intelipost_basic/settings/destination_range_start'));
            $max_range = (int)$this->removePostcodeFormat(Mage::getStoreConfig('intelipost_basic/settings/destination_range_end'));

            $validNewOrigin =  filter_var(
                                            $dest_postcode, 
                                            FILTER_VALIDATE_INT, 
                                            array(
                                                'options' => array(
                                                    'min_range' => $min_range, 
                                                    'max_range' => $max_range
                                                )
                                            )
                                        );
            if ($validNewOrigin)
            {
                return Mage::getStoreConfig('intelipost_basic/settings/new_origin_wh');
            }
            else
            {
                if (strlen($min_range) != strlen($max_range))
                {
                    if (strlen($min_range) == strlen($dest_postcode) && $dest_postcode >= $min_range)
                    {
                        return Mage::getStoreConfig('intelipost_basic/settings/new_origin_wh');
                    }
                    else if ($dest_postcode <= $max_range)
                    {   
                        return Mage::getStoreConfig('intelipost_basic/settings/new_origin_wh');
                    }
                }
            }
        }

        return Mage::getStoreConfig('intelipost_basic/settings/default_wh');
    }

    public function removePostcodeFormat($postcode)
    {
        return str_replace('-', null, trim($postcode));
    }
}

