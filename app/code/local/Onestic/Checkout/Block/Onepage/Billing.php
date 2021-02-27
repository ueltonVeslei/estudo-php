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

class Onestic_Checkout_Block_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing {

	/**
	 * Get quote billing address
	 *
	 * @return Mage_Sales_Model_Quote_Address
	 */
	public function getAddress() {
		return $this->getQuote()->getShippingAddress();
	}

	public function getCountryOptions() {
		$options = false;
		$useCache = Mage::app()->useCache( 'config' );
		if ( $useCache ) {
			$cacheId = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
			$cacheTags = array( 'config' );
			if ( $optionsCache = Mage::app()->loadCache( $cacheId ) ) {
				$options = unserialize( $optionsCache );
			}
		}

		if ( $options == false ) {
			$options = $this->getCountryCollection()->toOptionArray( FALSE );
			if ( $useCache ) {
				Mage::app()->saveCache( serialize( $options ), $cacheId, $cacheTags );
			}
		}
		return $options;
	}
	
	public function getAddressesHtmlSelect($type) {
		if ($this->isCustomerLoggedIn()) {
			$addressId = $this->getAddress()->getCustomerAddressId();
			if (empty($addressId)) {
				if ($type=='billing') {
					$address = $this->getCustomer()->getPrimaryBillingAddress();
				} else {
					$address = $this->getCustomer()->getPrimaryShippingAddress();
				}
				if ($address) {
					$addressId = $address->getId();
				}
			}
	
			$html = "";
			foreach ($this->getCustomer()->getAddresses() as $address) {
				$html .= "<li class='form-control'>
					<label for='{$type}_customer_address_".$address->getId()."'>
						<div class='input'>
							<input type='radio' name='{$type}_address_id' 
								id='{$type}_customer_address_".$address->getId()."' 
								value='".$address->getId()."' ".( ($address->getId() == $addressId) ? "checked='checked'" : "" )." 
								onclick='".$type.".newAddress(!this.value)' />
						</div>
						<div class='address'>
							<span>" . $address->getStreet1() . ", " . $address->getStreet2() . 
								( ($address->getStreet3()) ? " - " . $address->getStreet3() : "" ) . "</span>
							<span>" . $address->getStreet4() . "</span>
							<span>" . $address->getCity() . " - " . $address->getRegionCode() . "</span>
							<span>CEP: " . $address->getPostcode() . "</span> 
						</div>
					</label>
				</li>";
			}
			$html .= "<li class='form-control new'>
				<label for='{$type}_customer_address_new'>
					<div class='input'>
						<input type='radio' name='{$type}_address_id' 
							id='{$type}_customer_address_new' 
							value='' 
							onclick='".$type.".newAddress(true)' />
					</div>
					<div class='address'><span>Adicionar Novo</span></div>
				</label>
			</li>";
			 
			return $html;
		}
		return '';
	}

}