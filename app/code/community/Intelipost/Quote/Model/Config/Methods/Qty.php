<?php

class Intelipost_Quote_Model_Config_Methods_Qty
{        

public function toOptionArray()
{
	$i = 10;
	$option = array();

	for ($j = 1; $j <= $i; $j++)
	{
		$option[] = array('value' => $j, 'label' => $j );		  	
	}

	return $option;
}

}

