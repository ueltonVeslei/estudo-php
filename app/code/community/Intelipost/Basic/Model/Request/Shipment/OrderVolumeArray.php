<?php

class Intelipost_Basic_Model_Request_Shipment_OrderVolumeArray
// extends Varien_Object
{

    
    public $shipment_order_volume_number = 1;
    
    public $weight;
   
    public $volume_type_code = "box";

    public $width;

    public $height;

    public $length;

    public $products_nature;

    public $products_quantity;
   
    public $is_icms_exempt;

    public $tracking_code;

    public $shipment_order_volume_invoice;

    /**
     * fetch collected data to quote     
     *
     * @param Intelipost_Shipping_Model_Carrier_Shipping_Data
     * @return Intelipost_Model_Request_Quote
     */    
    public function fetchRequest($order, $trackRequest, $volumes_qty)
    {            
        $this->getDimensions($order->getAllItems());
        //$this->weight = $order->getWeight();
        $this->products_quantity = Mage::helper('quote')->getItemsCount($order->getAllItems());
        //$this->tracking_code = strtoupper($trackRequest['track_number']);        
        $this->is_icms_exempt =  (int)Mage::getStoreConfig ('intelipost_basic/settings/icms_tax_exempt'); // Mage::helper('quote')->getConfigData('icms_exempt') ? 'true' : 'false';
        $this->products_nature = Mage::getStoreConfig ('intelipost_basic/settings/products_nature'); // Mage::helper('quote')->getConfigData('products_nature');

        if (Mage::getStoreConfig('intelipost_push/general/nfe_required_create_intelipost'))
        {
            $this->shipment_order_volume_invoice = Mage::getModel('basic/request_shipment_orderVolumeInvoice');
            $this->shipment_order_volume_invoice->fetchRequest($order, $trackRequest, $volumes_qty);        
        }

        $collection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($order)
            ->load();

        $this->tracking_code = '';
        foreach ($collection as $shipment)
        {
            foreach ($shipment->getAllTracks () as $track)
            {
                $this->tracking_code = $track->getNumber ();

                break;
            }
        }

        if (!$this->tracking_code)
        {
            $intelipost_tracking = Mage::getModel("basic/trackings")->load($order->getIncrementId(), 'increment_id');

            if (count($intelipost_tracking->getData()) > 0)
            {
                $this->tracking_code = $intelipost_tracking->getCode();
            }
        }

        //$this->tracking_code = 'ABCDEF';
        return $this;

    }

    public function getDimensions($items)
    {
        $dimension = Mage::getModel('basic/package_dimension');
        $dimension->calcItemsDimension($items);

        $this->width = $dimension->getWidth();
        $this->height = $dimension->getHeight();
        $this->length = $dimension->getLength();
        $this->weight = $dimension->getWeigth();

        return $this;
    }
} 

