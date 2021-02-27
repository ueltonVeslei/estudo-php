<?php

class Intelipost_Basic_Model_Config_Product_Categories
{

public function toOptionArray ()
{
    $categories = Mage::helper ('basic')->getProductCategories ();

    $result = null;

    foreach ($categories as $_category)
    {
        $level = str_repeat (' - ', ceil(intval ($_category->getLevel ()) - 1));

        $result [] = array (
            'value' => $_category->getId (),
            'label' => $level . $_category->getName ()
        );
    }

    return $result;
}

}

