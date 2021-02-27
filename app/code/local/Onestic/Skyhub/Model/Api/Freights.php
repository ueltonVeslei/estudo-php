<?php
class Onestic_Skyhub_Model_Api_Freights extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'freights';
    
    public function freights($params) {
        return $this->get($this->_endpoint,$params);
    }
    
}