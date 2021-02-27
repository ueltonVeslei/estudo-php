<?php

    $this->startSetup();
    
   	$this->addAttribute
    (
        'catalog_product', 
        'enviar_para_zhao',
        array
        (
            'type'	   => 'int',
			'label'    => 'Enviar para Zhao?',
            'input'    => 'boolean',
			'visible'  => true,
            'required' => false,
			'default'  => 0,
            'position' => 1
        )
    );
    
    $this->endSetup();
