<?php
$this->startSetup();
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'category_brand', array(
    'group'         => 'General Information',
    'input'         => 'select',
    'type'          => 'text',
    'label'         => 'Fornecedor ou Marca',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'source' => 'categoryattribute/source_data',
));
 
$this->endSetup();