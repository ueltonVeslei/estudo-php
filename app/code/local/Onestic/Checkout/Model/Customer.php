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

class Onestic_Checkout_Model_Customer extends Mage_Customer_Model_Customer {

	const EXCEPTION_INVALID_EMAIL = 4;
	const EXCEPTION_INVALID_PASSWORD = 5;

	/**
	 * Authenticate customer
	 *
	 * @param  string $login
	 * @param  string $password
	 * @throws Mage_Core_Exception
	 * @return true
	 *
	 */
	public function authenticate( $login, $password ) {
		if (strpos($login, '@') === false) {
			$collection = Mage::getModel('customer/customer')->getCollection()
				->addFieldToFilter('taxvat',$login);
			$collection->getSelect()->limit(1);
			
			$customer = $collection->load()->getFirstItem();
			if (!$customer->getId()) {
				$collection = Mage::getModel('customer/customer')->getCollection()
					->addFieldToFilter('taxvat',preg_replace('/\D/','',$login));
				$collection->getSelect()->limit(1);
				$customer = $collection->load()->getFirstItem();
			}

			$this->load($customer->getId());
		} else {
			$this->loadByEmail( $login );
		}
		
		if ( $this->getConfirmation() && $this->isConfirmationRequired() ) {
			throw Mage::exception( 'Mage_Core', Mage::helper( 'customer' )->__( 'This account is not confirmed.' ), self::EXCEPTION_EMAIL_NOT_CONFIRMED
			);
		}
		if ( !$this->getEmail() ) {
			throw Mage::exception( 'Mage_Core', Mage::helper( 'customer' )->__( 'Invalid email.' ), self::EXCEPTION_INVALID_EMAIL
			);
		}
		if ( !$this->validatePassword( $password ) ) {
			throw Mage::exception( 'Mage_Core', Mage::helper( 'customer' )->__( 'Invalid password.' ), self::EXCEPTION_INVALID_PASSWORD
			);
		}
		Mage::dispatchEvent( 'customer_customer_authenticated', array(
			'model' => $this,
			'password' => $password,
		) );

		return true;
	}

}