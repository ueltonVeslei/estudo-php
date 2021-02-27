<?php

class Intelipost_Quote_Model_Config_Customer_Groups
{        

public function toOptionArray()
{
	return Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();		
}

}

