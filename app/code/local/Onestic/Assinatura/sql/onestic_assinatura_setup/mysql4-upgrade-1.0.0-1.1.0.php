<?php
/**
** CRIAÇÃO DE NOVOS ATRIBUTOS
**/

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
for ($i=1; $i <= 3; $i++) {
    $codigo = 'assinatura' . $i . '_valorfixo';
    if($setup->getAttributeId(Mage_Catalog_Model_Product::ENTITY,$codigo) === false) {
        $config = array(
            'position' => 1,
            'required'=> 0,
            'label' => 'Plano' . $i . ' de Assinatura - Valor Fixo',
            'type' => 'decimal',
            'input'=>'text',
            'apply_to'=>'simple,bundle,grouped,configurable',
            'note'=>'Valor fixo do plano de assinatura'
        );
        $setup->addAttribute('catalog_product', $codigo , $config);
    }
}

$installer->endSetup();