<?php

class Netreviews_Pla_Block_Adminhtml_Edit_Form extends Mage_Adminhtml_Block_Widget_Form{

  protected function _prepareForm(){
		// auto called by magento
		$form = new Varien_Data_Form(array(
	    	'id'        =>  'edit_form',
	    	'action'    =>  $this->getUrl('*/*/save', array('store' => $this->getRequest()->getParam('store'))),
	    	'method'    =>  'post',
	    	'enctype'   =>  'multipart/form-data'
		));
		
        $form->setUseContainer(true);
        $this->setForm($form);
		
        return parent::_prepareForm();
    }
}