<?php
    $this->startSetup();
    $this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'is_tag', array(
        'group'         => 'General',
        'input'         => 'select',
        'type'          => 'text',
        'label'         => 'É uma tag?',
        'backend'       => '',
        'visible'       => true,
        'required'      => false,
        'visible_on_front' => true,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'source' => 'tagmodule/source_data',
    ));
$this->endSetup();