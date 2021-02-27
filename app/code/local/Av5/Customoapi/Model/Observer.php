<?php
class Av5_Customoapi_Model_Observer
{
    public function checkHistory($observer)
    {
        $order = $observer->getEvent()->getOrder();
		if(!$order->getMarketplaceId()) {
            foreach ($order->getStatusHistoryCollection() as $status) {
                if (strpos($status->getComment(), 'Skyhub code:') !== false) {
                    $comment = str_replace('Skyhub code: ','',$status->getComment());
                    list($marketplace, $mkID) = explode('-',trim($comment));
                    $order->setMarketplaceId($mkID);
                    $order->setMarketplace($marketplace);
					$order->save();
                    break;
                }
            }
        }
    }
    
    public function toHtmlBefore(Varien_Event_Observer $observer)
    {
        /** @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();
        if ($block->getId() == 'sales_order_grid') {
             
            $block->addColumnAfter(
                'payment_method',
                array(
                    'header'   => Mage::helper('sales')->__('Forma de Pagamento'),
                    'align'    => 'left',
                    'type'     => 'text',
                    'index'    => 'method',
                    'filter_index'    => 'pm.method',
                ),
                'grand_total'
            );
    
            $block->sortColumnsByOrder();
        }
    }
    public function orderGridCollectionLoadBefore(Varien_Event_Observer $observer)
    {
    	$collection = $observer->getOrderGridCollection();
    	$select = $collection->getSelect();
    	$select->joinLeft(array('pm' => $collection->getTable('sales/order_payment')), 'pm.parent_id = main_table.entity_id',array('method'));
    }
    
}
