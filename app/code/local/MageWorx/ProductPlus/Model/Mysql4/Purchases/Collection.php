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
 * @package    MageWorx_ProductPlus
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Product extension
 *
 * @category   MageWorx
 * @package    MageWorx_ProductPlus
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_ProductPlus_Model_Mysql4_Purchases_Collection extends Mage_Sales_Model_Mysql4_Order_Item_Collection
{
    public function _construct()
    {
    	parent::_construct();
    }

    protected function _afterLoad()
    {
		parent::_afterLoad();

        foreach ($this as $item) {
        	$itemModel = Mage::getModel('sales/order_item')->load($item->getId());
                
				if($itemModel->getProductType() == 'virtual') {
				
	                switch($itemModel->getStatusName($itemModel->getStatusId())):
	                    case'Ordem': $options['status'] = 'Pendente'; break;
	                    case'Faturado': $options['status'] = 'Completo'; break;
	                    case'Misturado': $options['status'] = 'Completo'; break;
	                    default: $options['status'] = $itemModel->getStatusName($itemModel->getStatusId()); break;
	                endswitch;
						
				} else {
					
					switch($itemModel->getStatusName($itemModel->getStatusId())):
	                    case'Ordem': $options['status'] = 'Pendente'; break;
	                    case'Faturado': $options['status'] = 'Processando'; break;
	                    case'Misturado': $options['status'] = 'Completo'; break;
	                    default: $options['status'] = $itemModel->getStatusName($itemModel->getStatusId()); break;
	                endswitch;
					
				}

    		$item->addData($options);
        }
        return $this;
    }

	protected function _initSelect()
    {
    	parent::_initSelect();
		$this->getSelect()
	    	->joinLeft(
	            array('sales_order' => $this->getTable('sales/order')),
		    	'`main_table`.order_id = `sales_order`.entity_id',
		    	array('order_created_at' => 'created_at', 'entity_id', 'increment_id', 'store_id', 'subtotal', 'customer_id')
	        );

        return $this;
    }

    public function setProductFilter($id)
    {
    	$this->getSelect()
       		->where('`main_table`.product_id = ?', $id);
        return $this;
    }

    public function setParentItemIdFilter($parentId = null)
    {
    	if (is_null($parentId)) {
    		$this->getSelect()
       			->where("ISNULL(parent_item_id) OR parent_item_id = ''");
    	} else {
	    	$this->getSelect()
	       		->where('parent_item_id = ?', $parentId);
    	}
        return $this;
    }
}