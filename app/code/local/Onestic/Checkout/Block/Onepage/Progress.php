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

class Onestic_Checkout_Block_Onepage_Progress extends Mage_Checkout_Block_Onepage_Progress {

	public function _beforeToHtml() {
		$section = $this->getRequest()->getParam( 'section', false );
		switch( $section ) {
			case 'shipping':
				$this->getCheckout()->setStepData( 'shipping', 'complete', false );
			case 'billing':
				$this->getCheckout()->setStepData( 'billing', 'complete', false );
			case 'payment':
				$this->getCheckout()->setStepData( 'payment', 'complete', false );
				$this->getCheckout()->setStepData( 'shipping', 'complete', true );
		}
	}

	public function getActive() {
		if( Mage::helper( 'onestic_checkout' )->isVirtualOnly() ) {
			$active = $this->getRequest()->getParam( 'section', 'billing' );
		} else {
			$active = $this->getRequest()->getParam( 'section', 'shipping' );
		}

		return $active;
	}

	public function getShippingAddressHtml() {
		$address = $this->getShipping();
		$data = array(
			Mage::helper( 'onestic_checkout' )->getFullname( $address ),
			$address->getStreetFull(),
			$address->getCity() . ', ' . $address->getCountryModel()->getIso3Code() . ' ' . $address->getPostcode()
		);

		return join( '<br/>', $data );
	}

}