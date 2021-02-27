<?php

class Intelipost_Push_Block_Adminhtml_Nfes_Add_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
	{
		$form = new Varien_Data_Form(
		array(
		'id' => 'edit_form',
		'action' => $this->getUrl('*/*/save'),
		'method' => 'post',
		)
		);
	 
		$form->setUseContainer(true);
		
		$fieldset = $form->addFieldset('nfes', array('legend'=>Mage::helper('push')->__('Nfes')));
        
        $fieldset->addField('push_nfes', 'text', array(
                'name'=>'push_nfes',
                'class'=>'requried-entry'
                
        ));

        $form->getElement('push_nfes')->setRenderer(
            $this->getLayout()->createBlock('push/adminhtml_nfes_add_nfes')
        );

        $this->setForm($form);

		return parent::_prepareForm();
	}
}

