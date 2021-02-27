<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Catalog (Products)
 * @package    Av5_ProductGrid
 * @copyright  Copyright (c) 2015 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Av5_Customoapi_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{

	protected function _prepareCollection()	{
		parent::_prepareCollection();
		
		$collection = $this->getCollection();
		$select = $collection->getSelect();
		$select->joinLeft(array('pm' => $collection->getTable('sales/order_payment')), 'pm.parent_id = main_table.entity_id',array('method'));
		
		return $this;
	}
	
	protected function _prepareColumns() {
		parent::_prepareColumns();
		
		$this->addColumnAfter(
			'payment_method',
			array(
				'header'			=> Mage::helper('sales')->__('Forma de Pagamento'),
				'align'				=> 'left',
				'type'				=> 'text',
				'index'				=> 'method',
				'filter_index'		=> 'pm.method',
			),
			'grand_total'
		);
		
		$this->sortColumnsByOrder();		
		return $this;
	}
	
}