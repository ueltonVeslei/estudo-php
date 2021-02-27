<?php
class Onestic_Skyhub_Model_Api_Products extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'products';
    
    public function getCollection($params) {
        return $this->get($this->_endpoint,$params);
    }
    
    public function getProduct($id) {
        return $this->get($this->_endpoint . '/' . $id);
    }
    
    public function create($data) {
        return $this->post($this->_endpoint,$data);
    }
    
    public function update($id,$data) {
        return $this->put($this->_endpoint . '/' . $id,$data);
    }
    
    public function remove($id) {
        return $this->delete($this->_endpoint . '/' . $id);
    }

    public function addVariations($id,$data) {
        return $this->post($this->_endpoint . '/' . $id,$data);
    }
}