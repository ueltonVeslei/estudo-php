<?php
class Netreviews_Pla_Block_Adminhtml_Form_Export_Export extends Netreviews_Avisverifies_Block_Adminhtml_Form_Export_Export
{
    protected function _prepareForm() {
        parent::_prepareForm();
		$fieldset = $this->getForm()->getElement('export');
		$fieldset->removeField('sku');
		$fieldset->addField('sku', 'hidden', array(
		'required' => false,
		'name' => 'sku',
		'value' => 0,
		));
		return $this; // @return Mage_Adminhtml_Block_Widget_Form
    }

}