<?php

class Netreviews_Pla_Block_Adminhtml_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct(){
		// tabs section
        parent::__construct();
        $this->setId('netreviews_pla_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('PLA Information'));
	}

    protected function _prepareLayout(){
		$this->addTab('content_section', array(
			'label'     =>  $this->__('Content Settings'),
			'title'     =>  $this->__('Content Settings'),
			'content'   =>  $this->getLayout()->createBlock('netreviews_pla/adminhtml_edit_content')
							->setTemplate('netreviews/pla/content.phtml')->toHtml(),
		));
        return parent::_beforeToHtml();
	}
}