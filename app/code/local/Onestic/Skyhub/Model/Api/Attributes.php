<?php
class Onestic_Skyhub_Model_Api_Attributes extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'attributes';
    
    public function create($data) {
        $this->post($this->_endpoint,$data);
    }
    
    public function update($id,$data) {
        $this->put($this->_endpoint . '/' . $id,$data);
    }
    
}