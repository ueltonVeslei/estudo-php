<?php

class Intelipost_Basic_Model_Resource_Attribute_Source_List
{

public function getAllOptions()
{
    $attributes=Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();
    $attributes->setOrder('frontend_label','ASC');

    $_options=array();

    $_options[]=array(
        'label' => Mage::helper('basic')->__('No usage'),
        'value' => 0
    );    

    foreach ($attributes as $attr)
    {
        $label=$attr->getStoreLabel() ? $attr->getStoreLabel() : $attr->getFrontendLabel();

        if ('' != $label)
        {
            $_options[]=array('label' => $label,'value' => $attr->getAttributeCode());
        }
    }

    return $_options;
}

public function toOptionArray()
{
    return $this->getAllOptions();
}

}

