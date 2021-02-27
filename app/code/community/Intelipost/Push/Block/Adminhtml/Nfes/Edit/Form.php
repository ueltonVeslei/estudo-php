<?php

class Intelipost_Push_Block_Adminhtml_Nfes_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$push_nfes = Mage::registry('push_nfes_data');

		$form = new Varien_Data_Form(
		array(
		'id' => 'edit_form',
		'action' => $this->getUrl('*/*/save'),
		'method' => 'post',
		)
		);
	 
		$form->setUseContainer(true);
		$helper = Mage::helper('push');

		$fieldset = $form->addFieldset('nfes', array('legend'=>Mage::helper('push')->__('Nfes')));
        
        $fieldset->addField('id', 'hidden', array(
      	'name'               => 'id',
      	'value'              => $push_nfes->getId(),
		));

        $fieldset->addField('increment_id', 'text', array(
		'name' => 'increment_id',
		'label' => $helper->__('Increment Id'),
		'class' => 'validate-digits',
		'value' => $push_nfes->getIncrementId(),
		));		

		$fieldset->addField('series', 'text', array(
		'name' => 'series',
		'label' => $helper->__('Series'),
		'class' => 'validate-digits',
		'value' => $push_nfes->getSeries(),
		));		

		$fieldset->addField('number', 'text', array(
      	'label'     => $helper->__('Number'),
      	'name'      => 'number',
      	'value'    => $push_nfes->getNumber(),         
  		));	

  		$fieldset->addField('total', 'text', array(
		'name' => 'total',
		'label' => $helper->__('Total'),
		'value' => $push_nfes->getTotal()		
		));

		$fieldset->addField('cfop', 'text', array(
		'name' => 'cfop',
		'label' => $helper->__('CFOP'),		
		'value' => $push_nfes->getCfop(),
		));

		$fieldset->addField('created_at', 'date', array(
		'name' => 'created_at',
		'label' => $helper->__('Created At'),
		'value' => $push_nfes->getCreatedAt(),
		'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
		'format' => 'dd-MM-YYYY',
		));

		$fieldset->addField('key_nfe', 'text', array(
		'name' => 'key_nfe',
		'label' => $helper->__('Key'),
		'class' => 'validate-digits',
		'value' => $push_nfes->getKeyNfe(),
		));	

        $this->setForm($form);

		return parent::_prepareForm();
	}
}

