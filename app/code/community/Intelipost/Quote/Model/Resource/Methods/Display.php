<?php

class Intelipost_Quote_Model_Resource_Methods_Display
{

public function toOptionArray()
{
    $options = array();
    for ($i = 1; $i<=10; $i++)
    {
        $options[] = array('value' => $i, 'label' => $i);
    }
    
    return $options;
}

}