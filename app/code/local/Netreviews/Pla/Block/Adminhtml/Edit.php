<?php

class Netreviews_Pla_Block_Adminhtml_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        // save button , Main container
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'netreviews_pla';
        $this->_controller = 'adminhtml';
        $this->_headerText = $this->__('Google Shopping - Product Data (PLA)');

        $this->_updateButton('save', 'label', $this->__('Save'));

        $this->_addButton('add_new', array(
            'label'   => Mage::helper('catalog')->__('Export Product Info'),
            'onclick' => "setLocation('{$this->getUrl('*/*/exportProduct')}')",
            'class'   => 'add'
        ));
    }

    protected function _prepareLayout() {
        $this->_removeButton('back');
        $this->_removeButton('reset');
        return parent::_prepareLayout();
    }

}
