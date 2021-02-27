<?php
class Intelipost_Quote_Block_Adminhtml_Sales_Order_View_Tab_Intelipost
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_chat = null;
    public $rates = array();
    public $basicOrders;

    protected function _construct()
    {
        parent::_construct();

        $order = $this->getOrder();
        $status = $order->getStatus();
        
        $this->setQuoteId(0);
        $this->setFallBack(false);
        
        $this->basicOrders = Mage::getModel("basic/orders")->load($order->getId(), 'order_id');  
        if (count($this->basicOrders->getData()) > 0)
        {
            $this->setQuoteId($this->basicOrders->getDeliveryQuoteId());
            $this->setDeliveryMethodId($this->basicOrders->getDeliveryMethodId());
            
        }

        else if (Mage::helper('quote')->isFallBack($order->getShippingMethod()) || Mage::helper('quote')->isRequoteAllowed($status))
        {
            $this->setFallBack(true);
            
        }

        $this->setTemplate('intelipost/sales/order/view/tab/intelipost.phtml');
    }

    public function getTabLabel() {
        return $this->__('Intelipost');
    }

    public function getTabTitle() {
        return $this->__('Intelipost');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getOrder(){
        return Mage::registry('current_order');
    }

    public function getIntelipostQuoteUrl()
    {
        return 'https://secure.intelipost.com.br/quote-detail/' . $this->getQuoteId();
    }

    public function getOrderTrackingCode()
    {
        $order = $this->getOrder();
        $tracking = Mage::getModel('basic/trackings')->load($order->getIncrementId(), 'increment_id');
        $retorno = '-';
        if (count($tracking->getData()) > 0) 
        {
            $retorno = $tracking->getCode();
        }

        return $retorno;
    }

    public function getOrderNfes()
    {
        $order = $this->getOrder();
        $nfes = Mage::getModel('basic/nfes')->load($order->getIncrementId(), 'increment_id');
        $retorno = '-';
        if (count($nfes->getData()) > 0)
        {
            $retorno = $nfes->getNumber();
        }

        return $retorno;
    }

    public function getNfesCfop()
    {
        
        if (!$this->nfes)
        {
            $order = $this->getOrder();
            $this->nfes = Mage::getModel('basic/nfes')->load($order->getIncrementId(), 'increment_id');
        }

        $retorno = '-';
        if (count($this->nfes->getData()) > 0) 
        {
            $retorno = $this->nfes->getCfop();
        }

        return $retorno;
    }

    public function getNfesKey()
    {
        if (!$this->nfes)
        {
            $order = $this->getOrder();
            $this->nfes = Mage::getModel('basic/nfes')->load($order->getIncrementId(), 'increment_id');
        }

        $retorno = '-';
        if (count($this->nfes->getData()) > 0) 
        {
            $retorno = $this->nfes->getKeyNfe();
        }

        return $retorno;
    }

    public function getIntelipostOrderUrl()
    {
        $order = $this->getOrder();
        
        if (count($this->basicOrders->getData()) > 0)
        {
            return 'https://secure.intelipost.com.br/order-number/' . $order->getIncrementId();
        }

        return '-';
    }
    public function getNfesSeries()
    {
        if (!$this->nfes)
        {
            $order = $this->getOrder();
            $this->nfes = Mage::getModel('basic/nfes')->load($order->getIncrementId(), 'increment_id');
        }

        $retorno = '-';
        if (count($this->nfes->getData()) > 0) 
        {
            $retorno = $this->nfes->getSeries();
        }

        return $retorno;
    }

    public function getNfesCreatedAt()
    {
        if (!$this->nfes)
        {
            $order = $this->getOrder();
            $this->nfes = Mage::getModel('basic/nfes')->load($order->getIncrementId(), 'increment_id');
        }

        $retorno = '-';
        if (count($this->nfes->getData()) > 0) 
        {
            $retorno = $this->nfes->getCreatedAt();
        }

        return $retorno;
    }
    
    public function getShippingRates()
    {   
        if (!$this->getQuoteId())
        {
            return $this;
        }
        
        $collection_data = $this->getShippingRatesTable('intelipost');     
        $method_column = 'description';

        if (count($collection_data) == 0) 
        {
            $collection_data = $this->getShippingRatesTable('default');
            $method_column = 'method_description';
        }

        foreach ($collection_data as $col) 
        {        
            $this->rates[] = array( 'code' => $col['code'], 
                                    'description' => $col[$method_column],
                                    'estimated_days' => $col['intelipost_estimated_delivery_business_days'],
                                    'cost' => $col['intelipost_cost']);
        }

        return $this;
    }

    public function getRequoteMethod()
    {
        if (!$this->getQuoteId())
        {
            return $this;
        }
        
        $collection_data = $this->getShippingRatesTable('intelipost');     
        $method_column = 'description';

        if (count($collection_data) == 0) 
        {
            $collection_data = $this->getShippingRatesTable('default');
            $method_column = 'method_description';
        }

        $method = $this->getMethodForRequote($collection_data, $this->getDeliveryDays($this->getOrder()->getShippingDescription()));
        return $method;
    }

    public function getVolumes()
    {
        if (count($this->basicOrders->getData()) > 0)
        {
            return $this->basicOrders->getQtyVolumes();
        }

        return '-';
    }
    protected function getDeliveryDays($shippingDescription)
    {
        preg_match_all('!\d+!', $shippingDescription, $matches);
        foreach ($matches as $key => $value) 
        {
            $deliveryDays = ($value) ? (int)$value[0] : 100;
        }
        return $deliveryDays;
    }

    public function getMethodForRequote($quoteMethods, $fallbackDeliveryDays)
    {
        $methodsToAdd = array();
        $method_code = '';

        foreach ($quoteMethods as $key => $row) {
            $result[$key]  = $row['intelipost_cost'];
         }
        array_multisort($result, SORT_ASC, $quoteMethods);
        
        foreach($quoteMethods as $method)
        {        
            if ($method['intelipost_estimated_delivery_business_days'] <= $fallbackDeliveryDays)
            {
                if (count($methodsToAdd) == 0)
                {
                    $methodsToAdd[$method['code']] = $method;
                    $method_code = $method['code'];
                }
                else
                {
                    foreach ($methodsToAdd as $singleMethod) 
                    {
                        if ($singleMethod['intelipost_estimated_delivery_business_days'] > $method['intelipost_estimated_delivery_business_days'] && $singleMethod['intelipost_cost'] > $method['intelipost_cost'])
                        {
                            unset($methodsToAdd[$singleMethod['code']]);
                            $methodsToAdd[$method['code']] = $method;
                            $method_code = $method['code'];
                        }                    
                    }
                }
            }
        }

        return $method_code;
    }

    public function getShippingRatesTable($table_name)
    {
        if ($table_name == 'intelipost')
        {
            $collection = Mage::getModel ('quote/quote_address_shipping_rate')->getCollection ();
            $collection->addFieldToFilter('intelipost_quote_id', $this->getQuoteId());

            return $collection->getData();
        }
        else
        {
            $collection = Mage::getModel ('sales/quote_address_rate')->getCollection ();
            $collection->addFieldToFilter('intelipost_quote_id', $this->getQuoteId());

            return $collection->getData();
        }
    }

    public function getOrderStatuses()
    {
        $statuses = Mage::getModel('quote/config_order_status')->toOptionArray();

        return $statuses;
    }

    public function getIntelipostStatuses()
    {
        return array(   array(  'label' => 'Aguardando envio',
                                'value' => 'waiting' ),
                        array(  'label' => 'Envio criado',
                                'value' => 'created'),
                        array(  'label' => 'Envio pronto para despacho',
                                'value' => 'shipment ready'),
                        array(  'label' => 'Envio despachado',
                                'value' => 'shipped')
                            );
    }
}