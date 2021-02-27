<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Adminlogger
 * @copyright  Copyright (c) 2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */
class Magpleasure_Adminlogger_Block_Adminhtml_Adminlogger_Renderer_Orders extends Magpleasure_Adminlogger_Block_Adminhtml_Adminlogger_Renderer_Default
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("adminlogger/renderer/orders.phtml");
    }

    public function getDetails()
    {
        $details = parent::getDetails();
        $orders = array();
        foreach ($details as $detail){
            $orderId = $detail->getData('attribute_code');
            /** @var $order Mage_Sales_Model_Order  */
            $order = Mage::getModel('sales/order')->load($orderId);
            $incId = $order->getIncrementId();
            $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $orderId));
            $orders[] = "<a href=\"{$url}\" target=\"_blank\">#{$incId}</a>";
        }
        return $orders;
    }
}