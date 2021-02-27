<?php
 
class Onestic_Overcoupom_Block_Adminhtml_Sales_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'onestic_overcoupom';
        $this->_controller = 'adminhtml_sales_order';
        $this->_headerText = Mage::helper('onestic_overcoupom')->__('Pedidos - Cupons');
 
        parent::__construct();
        $this->_removeButton('add');
    }
}