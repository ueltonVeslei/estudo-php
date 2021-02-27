<?php
class Onestic_Skyhub_Model_Api_Variations extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'variations';
    
    public function variations($id) {
        return $this->get($this->_endpoint . '/' . $id);
    }
    
    public function update($id,$data) {
        $this->put($this->_endpoint . '/' . $id,$data);
    }
    
    public function remove($id) {
        $this->delete($this->_endpoint . '/' . $id);
    }
    
}