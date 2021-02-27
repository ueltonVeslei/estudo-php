<?php
/**
 * This file is part of Checkout.
 *
 * Checkout is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Checkout is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Checkout.  If not, see <http://www.gnu.org/licenses/>.
 */

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('customer', 'tipopessoa', array(
		'type' => 'varchar', //int
		'input' => 'text',  //select
		'label' => 'Tipo Pessoa',
		'global' => 1,
		'visible' => 1,
		'required' => 0,
		'user_defined' => 1,
		'default' => '',
		'visible_on_front' => 1
));

if (version_compare(Mage::getVersion(), '1.4.2', '>='))
{
	Mage::getSingleton('eav/config')
	->getAttribute('customer', 'tipopessoa')
	->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register','adminhtml_customer_address','customer_address_edit','customer_register_address'))
	->save();
}

$setup->addAttribute('customer', 'rgie', array(
		'type' => 'varchar',
		'input' => 'text',
		'label' => 'RG/IE',
		'global' => 1,
		'visible' => 1,
		'required' => 0,
		'user_defined' => 1,
		'default' => '',
		'visible_on_front' => 1
));

if (version_compare(Mage::getVersion(), '1.4.2', '>='))
{
	Mage::getSingleton('eav/config')
	->getAttribute('customer', 'rgie')
	->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','checkout_register','adminhtml_customer_address','customer_address_edit','customer_register_address'))
	->save();
}