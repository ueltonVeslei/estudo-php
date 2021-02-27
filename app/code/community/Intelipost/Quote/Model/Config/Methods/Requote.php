<?php

class Intelipost_Quote_Model_Config_Methods_Requote
{        

public function toOptionArray()
{
    return array(
        array(
            'value' => 'cheapest_cost',
            'label' => 'Cheapest Cost'
        ),
        array(
            'value' => 'cheapest_price',
            'label' => 'Cheapest Price'
        ),
        array(
            'value' => 'fastest',
            'label' => 'Fastest'
        ),
    );
}

}