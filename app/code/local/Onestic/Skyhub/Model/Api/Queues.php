<?php
class Onestic_Skyhub_Model_Api_Queues extends Onestic_Skyhub_Model_Api_Abstract {
    
    protected $_endpoint = 'queues/orders';
    
    public function getItem() {
        return $this->get($this->_endpoint);
    }
    
    public function remove($order_id) {
        $this->delete($this->_endpoint . '/' . $order_id);
    }
    
}