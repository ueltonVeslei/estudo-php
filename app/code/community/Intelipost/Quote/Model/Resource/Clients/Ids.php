<?php

class Intelipost_Quote_Model_Resource_Clients_Ids
{

public function toOptionArray()
{
    return array(
        array(
            'value' => 0,
            'label' => Mage::helper('quote')->__('Não'),
        ),
        array(
            'value' => 'cpf',
            'label' => Mage::helper('quote')->__('CPF'),
        ),
        array(
            'value' => 'cnpj',
            'label' => Mage::helper('quote')->__('CNPJ'),
        ),       
    );
}

}