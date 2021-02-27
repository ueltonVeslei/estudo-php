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

class MageWorx_Adminhtml_Block_Productplus_Product_Edit_Tab_Renderer_Email extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	private function _getCustomerEmail(Varien_Object $row)
	{
		$email = trim(Mage::getModel('customer/customer')->load($row->getCustomerId())->getEmail());
    	if (empty($email)) {
    		
			$order = Mage::getModel('sales/order')->load($row->getEntityId());
			$billing = $order->getBillingAddress();
			$email = $order->getCustomerEmail();
			
    		/*$collection = Mage::getResourceModel('sales/order_collection')
	            ->addAttributeToSelect('*')
				->addFieldToFilter('entity_id', $row->getEntityId());

			$data = $collection->exportToArray();
			if (isset($data[$row->getEntityId()]['customer_email'])) {
                $email = $data[$row->getEntityId()]['customer_email'];
			}*/
			
			//$email = trim("E-mail visitante");
    	}
    	return $email;
	}

    public function render(Varien_Object $row)
    {
    	$email = $this->_getCustomerEmail($row);
    	if (!empty($email)) {
            return '<a href="mailto:'. $email .'">'. $email .'</a>';
    	}
    }

	public function renderExport(Varien_Object $row)
    {
		return $this->_getCustomerEmail($row);
    }
}