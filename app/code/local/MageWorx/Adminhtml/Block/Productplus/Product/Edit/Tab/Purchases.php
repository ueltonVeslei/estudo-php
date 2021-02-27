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

class MageWorx_Adminhtml_Block_Productplus_Product_Edit_Tab_Purchases
	extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	protected $_product;

	public function __construct()
    {
        parent::__construct();
        $this->setId('productplus_purchases');
        $this->setDefaultSort('order_created_at', 'desc');
        $this->setUseAjax(true);

        $this->getCustomer();
    }

	public function getCustomer()
	{
	    if (!$this->_product) {
		    $productId = (int) $this->getRequest()->getParam('id');
	        $product = Mage::getModel('catalog/product');

	        if ($productId) {
	            $this->_product = $product->load($productId);
	        } else {
	        	$this->_product = new Varien_Object();
	        }
	    }
	    return $this->_product;
	}

    public function getGridUrl()
    {
        return $this->getUrl('mageworx/productplus_purchases/grid', array('_current' => true));
    }

	protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('productplus/purchases_collection')
	        ->setProductFilter($this->_product->getId())
	        ->setParentItemIdFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
		$this->addColumn('increment_id', array(
            'header'    => Mage::helper('productplus')->__('Order #'),
            'width'     => 100,
            'index'     => 'increment_id',
        ));

		$this->addColumn('customer_name', array(
            'header'    => Mage::helper('productplus')->__('Customer Name'),
            'index'     => 'customer_id',
        	'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_name',
        	'filter'    => false,
            'sortable'  => false,
        ));

		$this->addColumn('customer_email', array(
            'header'    => Mage::helper('productplus')->__('Customer Email'),
            'index'     => 'customer_id',
        	'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_email',
        	'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('order_created_at', array(
            'header'    => Mage::helper('productplus')->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('productplus')->__('Item Status'),
            'index'     => 'status',
        	'width'     => 80,
        	'align'     => 'center',
			'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('base_original_price', array(
            'header'    => Mage::helper('productplus')->__('Original Price'),
            'index'     => 'base_original_price',
			'align'     => 'right',
            'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_originalprice',
        	'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('base_price', array(
            'header'    => Mage::helper('productplus')->__('Price'),
            'index'     => 'base_price',
			'align'     => 'right',
        	'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_price',
        	'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('qty_ordered', array(
            'header'    => Mage::helper('productplus')->__('Qty'),
            'index'     => 'qty_ordered',
        	'width'     => 90,
        	'align'     => 'right',
        	'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_qty',
        	'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('subtotal', array(
            'header'    => Mage::helper('productplus')->__('Subtotal'),
            'index'     => 'subtotal',
			'align'     => 'right',
        	'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_subtotal',
        	'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('base_tax_amount', array(
            'header'    => Mage::helper('productplus')->__('Tax Amount'),
            'index'     => 'base_tax_amount',
        	'align'     => 'right',
			'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_taxamount',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('tax_percent', array(
            'header'    => Mage::helper('productplus')->__('Tax Percent'),
            'index'     => 'tax_percent',
			'align'     => 'right',
			'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_taxpercent',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('base_discount_amount', array(
            'header'    => Mage::helper('productplus')->__('Discount Amount'),
            'index'     => 'base_discount_amount',
        	'align'     => 'right',
			'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_discoutamount',
        	'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('base_row_total', array(
            'header'    => Mage::helper('productplus')->__('Row Total'),
            'index'     => 'base_row_total',
        	'align'     => 'right',
			'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_rowtotal',
            'filter'    => false,
            'sortable'  => false,
        ));

    	if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
                'header'     => Mage::helper('productplus')->__('Bought From'),
                'index'      => 'store_id',
				'align'      => 'center',
                'type'       => 'store',
                'store_view' => true,
				'renderer'   => ($this->getExport() ? 'mageworx/widget_grid_column_renderer_store_export' : ''),
            ));
        }

    	if (Mage::getSingleton('admin/session')->isAllowed('customer/manage')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('productplus')->__('Action'),
                	'index'     => 'stores',
                    'width'     => 70,
					'renderer'  => 'mageworx/productplus_product_edit_tab_renderer_viewcustomer',
                    'sortable'  => false,
                    'filter'    => false,
                    'is_system' => true,
            ));
        }
        $this->addExportType('mageworx/productplus_purchases/exportCsv', Mage::helper('productplus')->__('CSV'));
        $this->addExportType('mageworx/productplus_purchases/exportXml', Mage::helper('productplus')->__('XML'));

        return parent::_prepareColumns();
    }

	public function getXml()
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

		$n   = "\n";
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.$n;
        $xml.= '<items>'.$n;
        foreach ($this->getCollection() as $item) {
            $data = array();
            foreach ($this->_columns as $key => $column) {
                if (!$column->getIsSystem()) {
                   $data[$key] = str_replace(array('"', '\\'), array('""', '\\\\'), $column->getRowFieldExport($item));
                }
            }
            $xml.= $this->_toXml($data);
        }
        $xml.= '</items>'.$n;
        return $xml;
    }

	private function _toXml(array $arrAttributes = array(), $rootName = 'item')
    {
    	$n   = "\n";
        $xml = '';
        $xml.= '<'.$rootName.'>'.$n;
        foreach ($arrAttributes as $fieldName => $fieldValue) {
            $fieldValue = "<![CDATA[$fieldValue]]>";
            $xml.= "<$fieldName>$fieldValue</$fieldName>".$n;
        }
        $xml.= '</'.$rootName.'>'.$n;
        return $xml;
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('productplus')->__('Orders by Customers');
    }

    public function getTabTitle()
    {
        return Mage::helper('productplus')->__('Orders by Customers');
    }

    public function getAfter()
    {
        return 'customer_options';
    }

    public function canShowTab()
    {
        if (Mage::registry('product')->getId()) {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        if (Mage::registry('product')->getId()) {
            return false;
        }
        return true;
    }
}
