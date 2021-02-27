<?php
	
class Intelipost_Push_Block_Adminhtml_Nfes_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "push";
				$this->_controller = "adminhtml_nfes";
				$this->_updateButton("save", "label", Mage::helper("push")->__("Save"));
				$this->_updateButton("delete", "label", Mage::helper("push")->__("Delete"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("push")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("push_nfes_data") && Mage::registry("push_nfes_data")->getId() ){

				    return Mage::helper("push")->__("Edit Item '%d'", $this->htmlEscape(Mage::registry("push_nfes_data")->getId()));

				} 
				else{

				     return Mage::helper("push")->__("Add Item");

				}
		}
}

