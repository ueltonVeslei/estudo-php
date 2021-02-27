<?php
class Onestic_Skyhub_Model_Api_Statuses extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'statuses';
    
    public function statuses() {
        return $this->get($this->_endpoint);
    }
    
    public function create($data) {
        $this->post($this->_endpoint,$data);
    }
    
    public function update($id,$data) {
        $this->put($this->_endpoint . '/' . $id,$data);
    }
    
}