<?php 

class Intelipost_Basic_Model_Config_Dimensions_Attributes
extends Mage_Core_Model_Config_Data
{

public function save()
{
    $length = $this->getValue();
    $heigth = $this->getData('groups/product_attributes/fields/height/value');
    $width = $this->getData('groups/product_attributes/fields/width/value');

    Mage::log($length);
    Mage::log($heigth);
    Mage::log($width);

    if ($length == $heigth || $length == $width || $width == $heigth)
    {
    	$helper = Mage::helper('basic');

        Mage::throwException($helper->__('Dimensions values attributes can\'t be the same'));
    }

    parent::save();
}

}