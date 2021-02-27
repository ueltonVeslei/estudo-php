<?php

    $this->startSetup();
    
   	$this->addAttribute
    (
        'catalog_product', 
        'enviar_para_liquidacaovirtual',
        array
        (
            'type'	   => 'int',
			'label'    => 'Enviar para Liquidacaovirtual?',
            'input'    => 'boolean',
			'visible'  => true,
            'required' => false,
			'default'  => 0,
            'position' => 1
        )
    );
    
    $this->endSetup();
