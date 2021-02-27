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
      
class Magaya_Logistico_Model_Sales_Order_Shipment_Api_V2  extends Mage_Sales_Model_Order_Shipment_Api_V2
{
   
    public function create(
        $orderIncrementId, 
        $itemsQty = array(), 
        $comment = null, 
        $email = false, 
        $includeComment = false
    ) 
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $itemsQty = $this->_prepareItemQtyData($itemsQty);
        /**
          * Check order existing
          */
        if (!$order->getId()) {
             $this->_fault('order_not_exists');
        }

        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
             $this->_fault('data_invalid', Mage::helper('sales')->__('Cannot do shipment for order.'));
        }

         /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $order->prepareShipment($itemsQty);
        
        if ($shipment) {
             $notifyShipment = Mage::getStoreConfig('logistico/shipments/notify_shipment');
             
             if ($notifyShipment) {
                 $comment = Mage::getStoreConfig('logistico/shipments/shipment_comment');
                 $includeComment = true;
             }
           
            $shipment->register();
            $shipment->addComment($comment, $email && $includeComment);
            if ($notifyShipment) {
                $shipment->setEmailSent(true);
            } else {
                 $shipment->setEmailSent(false);
            }
            
            $shipment->getOrder()->setIsInProcess(true);
            
            try {
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save(); 
                if (Mage::getStoreConfig('logistico/shipments/capture')) {
                     $this->capture($order, $itemsQty);
                }
            } catch (Mage_Core_Exception $e) { 
                $this->_fault('data_invalid', $e->getMessage());
            }
            
            return $shipment->getIncrementId();
        }
        
        return null;
    }
    
    protected function capture($order, $qtys) 
    {
        // 'NotifyCustomer' must be "true" or "yes" to trigger an email
        
        if ($order->canInvoice()) {
            $notify = Mage::getStoreConfig('logistico/shipments/notify_invoice');
            $commentInvoice = Mage::getStoreConfig('logistico/shipments/invoice_comment');
            $commentInvoice = $commentInvoice ? $commentInvoice : "Invoice was generated successfully";
            $invoice = $order->prepareInvoice($qtys);
            $invoice->setRequestedCaptureCase($invoice->canCapture() ? 'online' : 'offline')
                    ->register() // captures & updates order totals
                    ->addComment($commentInvoice, $notify);
            $order->setIsInProcess(true); // updates status on save
            
            $transaction = Mage::getModel('core/resource_transaction');
            if (isset($invoice)) {
                // order has been captured, therefore has been modified
                $transaction->addObject($invoice)
                        ->addObject($order);
            }
            
            $transaction->save();
            if (isset($invoice)){
               $invoice->sendEmail($notify);
            }
            
        }
        
        
    }

}