<?php

/**
 * Magaya
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@logistico.com so we can send you a copy immediately.
 *
 *
 * @category   Integration
 * @package    Magaya_Logistico
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magaya_Logistico_Model_Sales_Order_Api_V2 extends Mage_Sales_Model_Order_Api_V2
{

    public function items($filters = null) 
    { 
        $orders = array();
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';

        /** @var $orderCollection Mage_Sales_Model_Mysql4_Order_Collection */
        $orderCollection = Mage::getModel("sales/order")->getCollection();
        
        $billingFirstnameField = "$billingAliasName.firstname";
        $billingLastnameField = "$billingAliasName.lastname";
        $billingTelephoneField = "$billingAliasName.telephone";
        $shippingFirstnameField = "$shippingAliasName.firstname";
        $shippingLastnameField = "$shippingAliasName.lastname";
        $orderCollection->addAttributeToSelect('*')
            ->addAddressFields()
            ->addExpressionFieldToSelect(
                'billing_firstname', "{{billing_firstname}}", array('billing_firstname' => $billingFirstnameField)
            )
            ->addExpressionFieldToSelect(
                'billing_lastname', "{{billing_lastname}}", array('billing_lastname' => $billingLastnameField)
            )
            ->addExpressionFieldToSelect(
                'billing_telephone', "{{billing_telephone}}", array('billing_telephone' => $billingTelephoneField)
            )
            ->addExpressionFieldToSelect(
                'shipping_firstname', "{{shipping_firstname}}", array('shipping_firstname' => $shippingFirstnameField)
            )
            ->addExpressionFieldToSelect(
                'shipping_lastname', "{{shipping_lastname}}", array('shipping_lastname' => $shippingLastnameField)
            )
            ->addExpressionFieldToSelect(
                'billing_name', "CONCAT({{billing_firstname}}, ' ', {{billing_lastname}})", 
                array('billing_firstname' => $billingFirstnameField, 'billing_lastname' => $billingLastnameField)
            )
            ->addExpressionFieldToSelect(
                'shipping_name', 'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})', 
                array('shipping_firstname' => $shippingFirstnameField, 'shipping_lastname' => $shippingLastnameField)
            );

        $giftTable = Mage::getSingleton('core/resource')->getTableName('giftmessage/message');
        $orderCollection->getSelect()->joinLeft(
            array('gm' => $giftTable),
            'main_table.gift_message_id = gm.gift_message_id',
            array(
                'gift_message_from' => 'sender',
                'gift_message_to'   => 'recipient',
                'gift_message_body' => 'message'
            )
        );
      
        $apiHelper = Mage::helper('api');
        $filters = $apiHelper->parseFilters($filters, $this->_attributesMap['order']);
        try {
            foreach ($filters as $field => $value) {
                $orderCollection->addFieldToFilter($field, $value);
            }

            $orderCollection->addFieldToFilter('logistico_synced', false);

            $range = Mage::getStoreConfig('logistico/orders/filter_date');
            $dateHelper = Mage::helper('mgylogistico/daterange');
            $orderCollection->addFieldToFilter('created_at', $dateHelper->getFromToDate($range));

            $stores = Mage::getStoreConfig('logistico/orders/stores');
            if ($stores && is_array($arrStores = explode(',', $stores))) {
                $orderCollection->addFieldToFilter('store_id', array('nin' => $arrStores));
            }

            $statuses = Mage::getStoreConfig('logistico/orders/order_status');
            $statuses = $statuses && is_array($arrStatus = explode(',', $statuses)) 
                    ? $arrStatus 
                    : array('processing,pending');
            $orderCollection->addFieldToFilter('status', array('in' => $statuses));
        } catch (Mage_Core_Exception $e) {
            $this->_fault('filters_invalid', $e->getMessage());
        }

        foreach ($orderCollection as $order) {
            $result = $this->_getAttributes($order, 'order');
            $result['gift_message_body'] = $order['gift_message_body'];
            $result['items'] = $this->_getOrderItems($order);
            $result['shipping_address'] = $order->getShippingAddress()->toArray();
            $orders[] = $result;
        }
        
        return $orders;
    }
    
    protected function _getOrderItems($order)
    {
        $items = array();
        
        foreach ($order->getAllItems() as $item) {
            // Don't retrieve gift message per item now
            /*if ($item->getGiftMessageId() > 0) {
                $item->setGiftMessage(
                    Mage::getSingleton('giftmessage/message')->load($item->getGiftMessageId())->getMessage()
                );
            }*/

            $items[] = $this->_getAttributes($item, 'order_item');
        }
        
        return $items;
    }
    /**
     * Marks order as Synced with Logistico
     * and changes status according to the config value
     * 
     * @param string $orderIncrementId
     * @return boolean
     */
    public function setAsSynced($orderIncrementId) 
    {
        $notify = true; // Set this value in config
        $comment = "Order sent successfully to the warehouse";

        $order = $this->_initOrder($orderIncrementId);

        $newStatus = Mage::getStoreConfig('logistico/orders/new_order_status');
        $status = $newStatus ? $newStatus : $order->getStatus();

        $historyItem = $order->addStatusHistoryComment($comment, $status);
        $historyItem->setIsCustomerNotified($notify)->save();

        try {
            $order->setLogisticoSynced(true);
            if ($notify && $comment) {
                $oldStore = Mage::getDesign()->getStore();
                $oldArea = Mage::getDesign()->getArea();
                Mage::getDesign()->setStore($order->getStoreId());
                Mage::getDesign()->setArea('frontend');
            }

            $order->save();
            $order->sendOrderUpdateEmail($notify, $comment);
            if ($notify && $comment) {
                Mage::getDesign()->setStore($oldStore);
                Mage::getDesign()->setArea($oldArea);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }
        
        return true;
    }
}
