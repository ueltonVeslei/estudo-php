<?php

class Doofinder_Feed_Model_Mysql4_Cron_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected function _construct()
    {
            $this->_init('doofinder_feed/cron');
    }
}
