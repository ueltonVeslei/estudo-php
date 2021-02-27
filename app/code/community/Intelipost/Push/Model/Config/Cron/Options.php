<?php

class Intelipost_Push_Model_Config_Cron_Options
{        

public function toOptionArray()
{
	$options = array();
	for ($i = 5; $i <=60 ; $i++)
	{
		if ( ($i % 5) == 0)
		{
			$options[] = array('label' => $i, 'value'=> $i);
		}
	}

	return $options;
}

}

