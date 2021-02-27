<?php

class Intelipost_Push_Block_Adminhtml_Trackings_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("push_form", array("legend"=>Mage::helper("push")->__("General")));

                $fieldset->addField("increment_id", "text", array(
                "label" => Mage::helper("push")->__("Order Increment ID"),
                "class" => "required-entry",
                "required" => true,
                "name" => "increment_id",
                ));
                $fieldset->addField("code", "text", array(
                "label" => Mage::helper("push")->__("Tracking Code"),
                "class" => "required-entry",
                "required" => true,
                "name" => "code",
                ));

				if (Mage::getSingleton("adminhtml/session")->getPushTrackingsData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getPushTrackingsData());
					Mage::getSingleton("adminhtml/session")->setPushTrackingsData(null);
				} 
				elseif(Mage::registry("push_trackings_data")) {
				    $form->setValues(Mage::registry("push_trackings_data")->getData());
				}
				return parent::_prepareForm();
		}
}

