<?php
class Briel_ReviewPlus_Block_Adminhtml_Ordertab extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	
	protected $_chat = null;

	protected function _construct() {
		parent::_construct();
		$this->setTemplate('briel_reviewplus/ordertab.phtml');
	}

    public function getTabLabel() {
		return $this->__('ReviewPlus');
    }

    public function getTabTitle() {
        return $this->__('ReviewPlus');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getOrder(){
        return Mage::registry('current_order');
    }
}
?>