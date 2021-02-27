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

class Onestic_Checkout_Model_Paypal_Config extends Mage_Paypal_Model_Config {

	/**
	 * BN code getter
	 * override method
	 *
	 * @param string $countryCode ISO 3166-1
	 */
	public function getBuildNotationCode($countryCode = null) {
		if (Mage::helper('onestic_checkout/edition')->isMageEnterprise()) {
			$newBnCode = 'Onestic_SI_MagentoEE';
		} elseif (Mage::helper('onestic_checkout/edition')->isMageCommunity()) {
			$newBnCode = 'Onestic_SI_MagentoCE';
		} else{
			$newBnCode = 'Onestic_SI_Custom';
		}
		//if you would like to retain the product and country code
		//E.g., Company_Test_EC_US
		//$bnCode = parent::getBuildNotationCode($countryCode);
		//$newBnCode = str_replace('Varien_Cart','Prjoect_Test',$bnCode);
		return $newBnCode;
	}

}
