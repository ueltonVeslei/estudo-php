<?php

$this->startSetup();

$this->addAttribute(
    'catalog_product', 
    'enviar_para_cliquefarma',
    array(
        'type'	   => 'int',
        'label'    => 'Enviar para Cliquefarma?',
        'input'    => 'boolean',
        'visible'  => true,
        'required' => false,
        'default'  => 1,
        'position' => 1
    )
);

$this->addAttribute(
    'catalog_product', 
    'enviar_para_multifarmas',
    array(
        'type'	   => 'int',
        'label'    => 'Enviar para Multifarmas?',
        'input'    => 'boolean',
        'visible'  => true,
        'required' => false,
        'default'  => 1,
        'position' => 1
    )
);

$this->addAttribute(
    'catalog_product', 
    'enviar_para_zoom',
    array(
        'type'	   => 'int',
        'label'    => 'Enviar para Zoom?',
        'input'    => 'boolean',
        'visible'  => true,
        'required' => false,
        'default'  => 1,
        'position' => 1
    )
);

$this->endSetup();