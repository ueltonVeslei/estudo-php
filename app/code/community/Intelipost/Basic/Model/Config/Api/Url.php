<?php 

class Intelipost_Basic_Model_Config_Api_Url
extends Mage_Adminhtml_Model_System_Config_Backend_Encrypted
{

public function save()
{
    $api_url = $this->getValue();

    parent::save();
}

}

