<?php
class Onestic_Skyhub_Model_Api_Categories extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'categories';
    
    public function create($data) {
        return $this->post($this->_endpoint,$data);
    }
    
    public function update($id,$data) {
        return $this->put($this->_endpoint . '/' . $id,$data);
    }
    
    public function remove($id) {
        return $this->delete($this->_endpoint . '/' . $id);
    }
    
    public function categories() {
        return $this->get($this->_endpoint);
    }
    
}