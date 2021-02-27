<?php
class Biostore_Importean_Block_Adminhtml_Ean
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
    	
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. foo_bar/adminhtml_baz
        $this->_blockGroup = 'importean';
        $this->_controller = 'adminhtml_ean';
        $this->_headerText = $this->__('Lista EAN dos Produtos importados por CSV');
         
        parent::__construct();
        
        $this->_removeButton('add');
        
    }
}