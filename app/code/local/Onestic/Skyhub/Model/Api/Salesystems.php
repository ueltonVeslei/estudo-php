<?php
class Onestic_Skyhub_Model_Api_Salesystems extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'sale_systems';
    
    public function salesystems() {
        return $this->get($this->_endpoint);
    }
    
}