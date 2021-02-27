<?php
class Biostore_Importean_Block_Index_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('importean_form');
        $this->setTitle('Importar Tabela EAN com CSV');
    }

    protected function _prepareForm()
    {

        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data')
        );

        $form->setHtmlIdPrefix('block_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('cms')->__('Upload File'), 'class' => 'fieldset-wide'));

        $fieldset->addField('arquivo', 'file', array(
            'name'      => 'arquivo',
            'label'     =>  'Arquivo CSV',
            'title'     =>  'Arquivo CSV',
            'required'  => true,
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
