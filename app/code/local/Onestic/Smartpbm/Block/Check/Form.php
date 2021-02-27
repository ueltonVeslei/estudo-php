<?php
class Onestic_Smartpbm_Block_Check_Form extends Mage_Catalog_Block_Product_Abstract {
    
    protected $_pbms = NULL;
    protected $_hasVidalink = NULL;
    protected $_hasSevenpdv = NULL;
    
    public function getCheckUrl() {
        return $this->getUrl('smartpbm/ajax/activate', array('_current' => true));
    }

    public function getRegisterUrl() {
        return $this->getUrl('smartpbm/ajax/register', array('_current' => true));
    }

    public function getEligibilityUrl() {
        return $this->getUrl('smartpbm/ajax/eligibility', array('_current' => true));
    }

    public function getPbm() {
        if (!$this->_pbms) {
            $productId = $this->getProduct()->getId();
            $this->_pbms = Mage::getResourceModel('smartpbm/products')->getPbms($productId);
        }
        
        return $this->_pbms;
    }
    
    public function hasVidalink() {
        if ($this->_hasVidalink == NULL) {
            $this->_hasVidalink = false;
            foreach ($this->getPbm() as $pbm) {
                if ($pbm == 'vidalink')
                    $this->_hasVidalink = true;
            }
        }
        
        return $this->_hasVidalink;
    }
    
    public function hasSevenpdv() {
        if ($this->_hasSevenpdv == NULL) {
            $this->_hasSevenpdv = false;
            foreach ($this->getPbm() as $pbm) {
                if ($pbm == 'sevenpdv')
                    $this->_hasSevenpdv = true;
            }
        }
    
        return $this->_hasSevenpdv;
    }
    
    public function getConvenios() {
        $list = Mage::getModel('smartpbm/pbms_vidalink')->listaConvenios();
        return $list->convenios->convenio;
    }
}
