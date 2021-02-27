<?php
require_once '../../../../../app/Mage.php';

Mage::app();

$installer = new Mage_Customer_Model_Entity_Setup('core_setup');

$installer->startSetup();


/*Remover atributos tipopessoa do OSC 4*/

if ($installer->getAttribute('customer', 'tipopessoa', 'attribute_id')) {
    $installer->removeAttribute('customer', 'tipopessoa');
    $installer->removeAttribute('customer_address', 'tipopessoa');
}


if (!$installer->getAttribute('customer', 'tipopessoa', 'attribute_id')) {
    $installer->addAttribute('customer', 'tipopessoa', array(
        'type' => 'int',
        'input' => 'select',
        'label' => 'Tipo de Pessoa',
        'global' => 1,
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'sort_order' => 95,
        'visible_on_front' => 1,
        'source' => 'eav/entity_attribute_source_table',
        'option' => array(
            'values' => array('Física', 'Jurídica'),
        ),
    ));
    if (version_compare(Mage::getVersion(), '1.6.0', '<=')) {
        $customer = Mage::getModel('customer/customer');
        $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
        $installer->addAttributeToSet('customer', $attrSetId, 'General', 'tipopessoa');
    }
    if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
        Mage::getSingleton('eav/config')
                ->getAttribute('customer', 'tipopessoa')
                ->setData('used_in_forms', array('adminhtml_customer', 'customer_account_create', 'customer_account_edit', 'checkout_register'))
                ->save();
    }
}
if (!$installer->getAttribute('customer', 'ie', 'attribute_id')) {

    $installer->addAttribute('customer', 'ie', array(
        'input' => 'text',
        'type' => 'varchar',
        'label' => 'IE (Inscrição Estadual)',
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
    ));

    if (version_compare(Mage::getVersion(), '1.6.0', '<=')) {
        $customer = Mage::getModel('customer/customer');
        $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
        $installer->addAttributeToSet('customer', $attrSetId, 'General', 'ie');
    }
    if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
        Mage::getSingleton('eav/config')
                ->getAttribute('customer', 'ie')
                ->setData('used_in_forms', array('adminhtml_customer', 'customer_account_create', 'customer_account_edit', 'checkout_register'))
                ->save();
    }
}


$firstname = Mage::getModel('eav/entity_attribute')
           ->loadByCode('customer', 'firstname')
           ->setIsRequired(true)
           ->save();

$lastname = Mage::getModel('eav/entity_attribute')
           ->loadByCode('customer', 'lastname')
           ->setIsRequired(true)
           ->save();

$dob = Mage::getModel('eav/entity_attribute')
           ->loadByCode('customer', 'dob')
           ->setIsRequired(true)
           ->save();

$telephone = Mage::getModel('eav/entity_attribute')
           ->loadByCode('customer_address', 'telephone')
           ->setIsRequired(true)
           ->save();

$city = Mage::getModel('eav/entity_attribute')
           ->loadByCode('customer_address', 'city')
           ->setIsRequired(true)
           ->save();

$street = Mage::getModel('eav/entity_attribute')
           ->loadByCode('customer_address', 'street')
           ->setIsRequired(true)
           ->save();

var_dump('Done!');

$installer->endSetup();