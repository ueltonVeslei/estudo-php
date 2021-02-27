<?php
class Av5_Correiospro_Block_Adminhtml_Promotion_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'av5_correiospro';
		$this->_controller = 'adminhtml_promotion';

		$this->_mode        = 'edit';

		$this->_addButton('saveandcontinue', array(
				'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
				'onclick'   => 'saveAndContinueEdit()',
				'class'     => 'save',
		), -100);

		$this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";

	}

	public function getHeaderText()
	{
		if( Mage::registry('correiospromo_data') && Mage::registry('correiospromo_data')->getId() ) {
			return Mage::helper('av5_correiospro')->__("Editar Regra %s (%s)", $this->htmlEscape(Mage::registry('correiospromo_data')->getNome()), $this->htmlEscape(Mage::registry('correiospromo_data')->getId()));
		} else {
			return Mage::helper('av5_correiospro')->__('Nova Regra');
		}
	}
}