<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * MageWorx Adminhtml extension
 *
 * @category   MageWorx
 * @package    MageWorx_Adminhtml
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Adminhtml_Block_Productplus_Product_Edit_Tab_Renderer_Name extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$name = trim(Mage::getModel('customer/customer')->load($row->getCustomerId())->getName());
    	if (empty($name)) {
    		
			$order = Mage::getModel('sales/order')->load($row->getEntityId());
			$billing = $order->getBillingAddress();
			$name = 'Visitante: ' . $billing->getFirstname() . ' ' . $billing->getLastname();
		
			/*	
    		$collection = Mage::getResourceModel('sales/order_collection')
	            ->addAttributeToSelect(array())
	            ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
	            ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
	            ->addExpressionAttributeToSelect('billing_name',
	                'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})',
	                array('billing_firstname', 'billing_lastname'))
	             ->addFieldToFilter('entity_id', $row->getEntityId());

			$data = $collection->getData();
			$name = Mage::helper('productplus')->__('Guest');
			if (isset($data[0]['billing_name'])) {
				$name .= ': '.$data[0]['billing_name'];
			}
			//$name = trim("Nome visitante");
			 * 
			 */
    	}
        return $name;
    }
}