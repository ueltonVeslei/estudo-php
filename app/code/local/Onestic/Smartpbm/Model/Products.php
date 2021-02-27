<?php
class Onestic_Smartpbm_Model_Products extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('smartpbm/products');
    }

    protected function _clearData()
    {
        $this->_data = [];
        return $this;
    }
}