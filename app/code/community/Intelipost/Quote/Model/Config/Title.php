<?php 

class Intelipost_Quote_Model_Config_Title
extends Mage_Core_Model_Config_Data
{

public function save()
{
    $customizeTitle = $this->getValue();

   	if ((strpos($customizeTitle, '%s') === false) || (strpos($customizeTitle, '%d') === false))
   	{
   		$helper = Mage::helper('quote');
   		Mage::throwException($helper->__('%s and %d are required on title string'));
   	}
    parent::save();
}

}