<?php

class Intelipost_Push_Block_Adminhtml_Trackings_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("push_trackings_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("push")->__("Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("push")->__("General"),
				"title" => Mage::helper("push")->__("General"),
				"content" => $this->getLayout()->createBlock("push/adminhtml_trackings_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}

