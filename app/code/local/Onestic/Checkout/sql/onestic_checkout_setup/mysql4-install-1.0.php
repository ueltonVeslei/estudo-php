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

Mage::getModel( 'adminnotification/inbox' )
		->setSeverity( Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE )
		->setTitle( '"Onestic Checkout" instalado com sucesso.' )
		->setDateAdded( gmdate( 'Y-m-d H:i:s' ) )
		->setUrl( 'http://www.onestic.com' )
		->setDescription( 'Módulo "Onestic Checkout" instalado com sucesso. Para configuração visite: Sistema / Configuração / Onestic / Checkout' )
		->save();

$installer->endSetup();
