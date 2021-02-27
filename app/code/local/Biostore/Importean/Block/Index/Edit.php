<?php
class Biostore_Importean_Block_Index_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
    	parent::__construct();
        $this->_blockGroup = 'importean';
        $this->_mode = 'edit';
		$this->_controller = 'index';
		$this->_headerText = Mage::helper('importean')->__('Edit Form');
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return 'Importar EAN com CSV';
    }

}
