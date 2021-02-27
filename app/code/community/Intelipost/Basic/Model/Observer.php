<?php

class Intelipost_Basic_Model_Observer
{

public function orderViewPageButton( Varien_Event_Observer $observer )
{
	$block = $observer->getEvent()->getData('block');

    if(preg_match("/_Block_Sales_Order_View$/is", get_class($block))
        && $block->getRequest()->getControllerName() == 'sales_order')
    {
		$config = Mage::getStoreConfig('carriers/intelipost/active');
		$inteliposTracking = Mage::getStoreConfig('basic/settings/tracking');
		
		if (!$config) return;

		$block->updateButton('order_ship', 'name', 'shipment');

		$order = $block->getOrder();
		//echo "<pre>"; print_r($order->getData()); exit;
		if ($order->canShip()
        && !$order->getForcedDoShipmentWithInvoice() && preg_match("/^intelipost/is", $order->getShippingMethod()) && $inteliposTracking) 
        {			
			$block->updateButton('order_ship', 'onclick', 'setLocation(\'' . $block->getUrl('*/sales_order_shipment/new', array('intellipost'=>'true')) . '\')');

			//$block->removeButton('order_ship');

		}
    }
}

/*
public function saveOrderAfter($observer)
{
    $order_id = $observer->getEvent()->getOrder ()->getId ();

    $resource = Mage::getSingleton ('core/resource');

    $collection = Mage::getModel ('sales/quote_address_rate')->getCollection ();
    $select = $collection->getSelect();
    $select->join (array('sfqa' => $resource->getTableName ('sales/quote_address')), 'sfqa.address_id = main_table.address_id', array())
    ->join (array('sfo' => $resource->getTableName ('sales/order')), 'sfo.quote_id = sfqa.quote_id', array())
    ->reset(Zend_Db_Select::COLUMNS)->columns (array ('main_table.*'))
    ->where ("sfqa.address_type = 'shipping' and main_table.code = sfo.shipping_method and sfo.entity_id = {$order_id}");

    $item = $collection->getFirstItem();
    $intelipost_quote_id = $item->getIntelipostQuoteId();
	if (!empty ($intelipost_quote_id) && intval ($intelipost_quote_id) > 0)
	{
        $intelipost_order = Mage::getModel('basic/orders')->load ($order_id, 'order_id');
		$data = array('order_id'           => $order_id,
					  'delivery_quote_id'  => $intelipost_quote_id,
					  'delivery_method_id' => $item->getMethod (),
					  'status'             => 'waiting');
		$intelipost_order->addData ($data)->save ();
	}

	return $observer;
}*/

public function saveOrderAfter($observer)
{
   	$order = $observer->getEvent()->getOrder();
  // 	if ($order->getBaseTotalDue() == 0 || $order->getTotalPaid() > 0)
  // 	{
  // 		return;
  // 	}
    if($order->getExportProcessed()){ //check if flag is already set.
        return;
    }

   	$order_id = $order->getId();
   	$intelipost_order = Mage::getModel('basic/orders')->load ($order_id, 'order_id');

   	if ($order->hasInvoices() && count($intelipost_order->getData()) > 0)
   	{
   		return;
   	}

    $order_id = $order->getId();

    $useShippingRate = Mage::helper('quote')->getConfigData('shipping_rate');

    $resource = Mage::getSingleton ('core/resource');

    $targetModel = $useShippingRate ? 'quote/quote_address_shipping_rate' : 'sales/quote_address_rate';
    $collection = Mage::getModel ($targetModel)->getCollection ();
    $select = $collection->getSelect();
    $select->join (array('sfqa' => $resource->getTableName ('sales/quote_address')), 'sfqa.address_id = main_table.address_id', array())
    ->join (array('sfo' => $resource->getTableName ('sales/order')), 'sfo.quote_id = sfqa.quote_id', array())
    ->reset(Zend_Db_Select::COLUMNS)->columns (array ('main_table.*'))
    ->where ("sfqa.address_type = 'shipping' and main_table.code = sfo.shipping_method and sfo.entity_id = {$order_id}");

    try
    {
	    $item = $collection->getFirstItem();
	    $intelipost_quote_id = $item->getIntelipostQuoteId();

	    //bonitas web
	    $mtitle = $item->getMethodTitle();
	    $mprice = $item->getPrice();

	    //$intelipost_order = Mage::getModel('basic/orders')->load ($order_id, 'order_id');

		if (!empty ($intelipost_quote_id) && intval ($intelipost_quote_id) > 0 && count($intelipost_order->getData()) == 0)
		{
	        //$intelipost_order = Mage::getModel('basic/orders')->load ($order_id, 'order_id');
            
	        $volumes = Mage::helper('basic')->getOrderQtyVolumes($order_id);

			$data = array('order_id'          				 => $order_id,
						  'delivery_quote_id'  				 => $intelipost_quote_id,
						  'delivery_method_id'				 => $item->getMethod (),
						  'delivery_business_day'  			 => $item->getIntelipostEstimatedDeliveryBusinessDays(),
						  'shipping_cost'					 => $item->getIntelipostCost(),
						  'qty_volumes'						 => $volumes,
						  'status'             				 => 'waiting');

			$intelipost_order->addData ($data)->save ();

			if (Mage::helper('quote')->getConfigData('concat_quote_id'))
			{
				$shippingMethod = $order->getShippingMethod();
		        $shippingMethod .= '_' . $intelipost_quote_id;
		        $order->setShippingMethod($shippingMethod);
		        $order->save();
	    	}

	    	$order->setShippingDescription($mtitle);
	    	$order->setBaseShippingAmount($mprice);
	    	$order->setShippingAmount($mprice);
		    $order->save();
		}
        elseif(!($intelipost_quote_id) && count($intelipost_order->getData()) == 0 && Mage::helper('quote')->getConfigData('use_requote'))
        {
            $request = Mage::getModel('quote/quote_request');
            $request->processRequestQuote($order_id, $intelipost_quote_id);
            $order->setShippingDescription($request->getShippingDescription());
            $order->setShippingMethod($request->getShippingMethod());
            $order->save();
        }
	}
	catch (Exception $e)
	{
		Mage::log($e->getMessage());
	}

	return $observer;
}

	public function prepareLayoutAfter($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!$block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs) {
            return;
        }

        $tabs = $block->getTabsIds();
        foreach ($tabs as $key => $value) 
        {
        	if (strpos($value, 'group') !== false)
        	{
        		$group_exp = explode('_', $value);
        		$group = Mage::getModel('eav/entity_attribute_group')->load($group_exp[1]);
        		if ($group->getAttributeGroupName() == 'Intelipost')
        		{
        			$block->removeTab($value);
        		}
        	}
        }
    }

/*
public function saveModuleConfig($observer)
{
	$object = $observer->getEvent()->getData('object');
	$section = $object->getData('section');

	//Mage::log($object);
	if (!in_array($section, MAge::helper('basic')->getIntelipostModules()))
	{
		return $observer;
	}

	$writeConfig = Mage::getModel('basic/class_intelipost_write_config');

	foreach($object->getData('groups') as $key => $value)		
	{
		if (!$writeConfig->isIntelipostConfig($section, $key)) {
			return $observer;
		}

		
		//Mage::log($writeConfig->getXmlConfig());
		foreach ($value['fields'] as $fieldKey => $fieldValue) 
		{
			foreach ($writeConfig->getXmlConfig()->default as $default) 
			{
				foreach ($default as $sectiontkey => $sectionvalue) 
				{
					if ($section == $sectiontkey)
					{
						foreach ($sectionvalue as $groupkey => $groupvalue) 
						{
							if ($groupkey == $writeConfig->getGroup())
							{
								foreach ($groupvalue->children() as $child)
								{
									if ($child->getName() == $fieldKey)
									{
										$child->{0}  = $fieldValue['value'];
									}																					
        						}
							}
						}
					}
				}					
			}
		}

		if ($section == 'carriers' && $writeConfig->getGroup() == 'intelipost') {
			$writeConfig->saveXml();
		}		
	}

	$writeConfig->saveXml();	

	return $observer;
}*/

}

