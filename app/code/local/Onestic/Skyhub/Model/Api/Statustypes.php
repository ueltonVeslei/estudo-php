<?php
class Onestic_Skyhub_Model_Api_Statustypes extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'status_types';
    
    public function statustypes() {
        return $this->get($this->_endpoint);
    }
    
}