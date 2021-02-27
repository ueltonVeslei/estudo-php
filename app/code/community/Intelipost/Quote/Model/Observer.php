<?php
/**
 * @category   Observer
 * @package    Intelipost_Shipping
 * @copyright  Copyright (c) 2014 Goom
 * @author     Goom <Goom.com.br>
 */
class Intelipost_Quote_Model_Observer 
{
	public function addRequoteAction($observer)
	{
		$block = $observer->getEvent()->getBlock();

		//$className = Mage::getEdition() == 'Enterprise'? 'Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction' : 'Mage_Adminhtml_Block_Widget_Grid_Massaction';
        $className = 'Mage_Adminhtml_Block_Widget_Grid_Massaction';
		//Mage::log(get_class($block));
	    if(get_class($block) == $className
	        && $block->getRequest()->getControllerName() == 'sales_order')
	    {
	    	if (Mage::helper('quote')->getConfigData('use_requote'))
	    	{
		        $block->addItem('requote', array(
		            'label' => Mage::helper('quote')->__('Generate Requote'),
		            'url' => Mage::helper('adminhtml')->getUrl('adminhtml/quote_quote/massRequote'),
		        ));
	    	}
	    }


	    return $observer;
	}

	
    public function changeOrderShippingDescription($observer)
    {
        try
        {
            $order  = $observer->getEvent()->getOrder();
            $quote  = $observer->getEvent()->getQuote();
            $rates  = $quote->getShippingAddress()->getShippingRatesCollection();
            $method = explode('_', $order->getShippingMethod());
            
            if (!is_array($method)) return;

            if ($method[0] != 'intelipost') return;

            if (Mage::helper('quote')->getConfigData('use_economic_express_method'))
            {
                foreach ($rates as $rate) 
                {
                    if ($rate->carrier == 'intelipost' && $rate->method == (int)$method[1])
                        $order->setShippingDescription($rate->method_description);
                }
            }
        }
        catch (Exception $e)
        {
            Mage::log($e->getMessage());
        }       
    }    

	public function addColumnToResource($observer)
	{
		if (Mage::helper('quote')->getConfigData('ship_method_column'))
	    {
			$collection = $observer->getEvent()->getData('order_grid_collection');		
			
            $collection->getSelect()->join(  array('sfo' => 'sales_flat_order'), 
                                            'main_table.entity_id = sfo.entity_id',
                                            array( 'sfo.shipping_method' => 'sfo.shipping_method', 
                                                    'sfo.status'         => 'sfo.status',
                                                    'sfo.grand_total'    => 'sfo.grand_total',
                                                    'sfo.entity_id'      => 'sfo.entity_id'));   

            if ($where = $collection->getSelect()->getPart('where')) 
            {
            
                foreach ($where as $key=> $condition) 
                {
                    if (strpos($condition, 'status')) {
                        $value       = explode('=', trim($condition, ')'));
                        $value       = trim($value[1], "' ");
                        $where[$key] = "(main_table.status = '$value')";
                    }

                    if (strpos($condition, 'grand_total')) {
                        $value       = explode('=', trim($condition, ')'));
                        $value       = trim($value[1], "' ");
                        $where[$key] = "(main_table.grand_total = '$value')";
                    }

                    if (strpos($condition, 'store_id')) {
                        $value       = explode('=', trim($condition, ')'));
                        $value       = trim($value[1], "' ");
                        $where[$key] = "(main_table.store_id = '$value')";
                    }

                    if (strpos($condition, 'increment_id')) {
                        $value       = explode('LIKE', trim($condition, ')'));
                        $value       = trim($value[1], "' ");
                        $where[$key] = "(main_table.increment_id LIKE '$value')";
                    }

                    if (strpos($condition, 'grand_total')) {
                        $value       = explode('LIKE', trim($condition, ')'));
                        $replace     = str_replace('base_grand_total', 'main_table.base_grand_total', $value[0]);
                       // $value       = trim($value[1], "' ");
                        $where[$key] = $replace . ")";
                    }
                }

                $collection->getSelect()->setPart('where', $where);
            }                                                                         
		}
		
	}

	public function addLayerLayoutHandle(Varien_Event_Observer $observer)
    {
        //$update = $observer->getEvent()->getLayout()->getUpdate();
        //$handles = $update->getHandles();

        //if (in_array('adminhtml_sales_order_view', $handles))        
        //{
            //$update->addHandle('intelipost_order_info_handle');                
        //}
    }

    public function addColumnToOrderGrid(Varien_Event_Observer $observer)
    {
        if (Mage::helper('quote')->getConfigData('ship_method_column'))
        {
        	$block = $observer->getBlock();
        	if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid)
            {
            	$block->addColumnAfter(
                    'shipping_method', array(
                    'header'=> Mage::helper('sales')->__('Shipping Method'),
                    'width' => '80px',
                    'filter_index' => 'sfo.shipping_method',
                    'type'  => 'text',
                    'index' => 'sfo.shipping_method',
                    ), 'shipping_name');
            }
        }
    }
    public function restrictCollection(Varien_Event_Observer $observer)
    {
    	$collection = $observer->getCollection();
        if ($collection instanceof Mage_Core_Model_Resource_Store_Collection)
            return;

        if ($collection instanceof Mage_Sales_Model_Resource_Order_Grid_Collection)
        {
            $collection->getSelect()->join(array('sfo' => 'sales_flat_order'), 'main_table.entity_id = sfo.entity_id',array('sfo.shipping_method' => 'sfo.shipping_method'));
        }
    }

    public function requoteOrders()
    {
        Mage::log('passed here');
    }
}