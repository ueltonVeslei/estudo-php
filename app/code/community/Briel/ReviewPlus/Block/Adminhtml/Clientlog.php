<?php
 
class Briel_ReviewPlus_Block_Adminhtml_Clientlog extends Mage_Adminhtml_Block_Widget_Grid_Container {
 
    public function __construct() {
        parent::__construct();
        $this->_controller = 'adminhtml_clientlog';
        $this->_blockGroup = 'reviewplus';
        $this->_headerText = Mage::helper('reviewplus')->__('Customer log');
		$this->removeButton('add');
    }
	
	protected function _prepareLayout() {
       $this->setChild( 'grid',
           $this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid',
           $this->_controller . '.grid')->setSaveParametersInSession(true));
       return parent::_prepareLayout();
	}
}
?>