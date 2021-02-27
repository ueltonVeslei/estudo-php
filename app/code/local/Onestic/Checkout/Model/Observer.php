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

class Onestic_Checkout_Model_Observer {

	/**
	 * If the order was placed via guest checkout, here we are still linking the order to the correct customer id based on the email
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function checkout_submit_all_after( $observer ) {
		if( !Mage::helper( 'onestic_checkout' )->getConfigData( 'options/link_guest_orders' ) )
			return;

		/** @var Mage_Sales_Model_Order $order */
		$order = $observer->getEvent()->getOrder();
		/** @var Mage_Sales_Model_Quote $order */
		$quote = $observer->getEvent()->getQuote();

		if( !$order->getCustomerId() ) {
			$customer = Mage::getModel( 'customer/customer' );
			$customer->setWebsiteId( Mage::app()->getWebsite()->getId() );

			$customer->loadByEmail( $quote->getCustomerEmail() );

			if( $customer->getId() ) {
				$order->setCustomer( $customer );
				$order->setCustomerId( $customer->getId() );
				$order->setCustomerIsGuest( false );
				$order->setCustomerGroupId( $customer->getGroupId() );
				$order->setCustomerEmail( $customer->getEmail() );
				$order->setCustomerFirstname( $customer->getFirstname() );
				$order->setCustomerLastname( $customer->getLastname() );
				$order->setCustomerMiddlename( $customer->getMiddlename() );
				$order->setCustomerPrefix( $customer->getPrefix() );
				$order->setCustomerSuffix( $customer->getSuffix() );
				$order->setCustomerTaxvat( $customer->getTaxvat() );
				$order->setCustomerGender( $customer->getGender() );
				$order->save();
			}
		}
	}

	public function addLayoutHandleForPaymentExtensionsCompatibility( $observer ) {
		$update = $observer->getEvent()->getLayout()->getUpdate();
		$handles = $update->getHandles();
		// Awesome Checkout Virtual Products Support Extension
		if ( Mage::helper( 'onestic_checkout' )->isVirtualOnly() ) {
			$update->addHandle( 'onestic_checkout_virtual' );
		}
		// Braintree handle
		if ( Mage::helper( 'onestic_checkout/edition' )->isExtensionEnabled( 'Braintree' ) && in_array( 'checkout_onepage_review', $handles ) ) {
			$update->addHandle( 'onestic_checkout_braintree_checkout_onepage_review' );
		}
		// Braintree handle
		if ( Mage::helper( 'onestic_checkout/edition' )->isExtensionEnabled( 'Braintree_Payments' ) && in_array( 'checkout_onepage_review', $handles ) ) {
			$update->addHandle( 'onestic_checkout_braintree_payments_checkout_onepage_review' );
		}
		// Sagepay handle
		if ( Mage::helper( 'onestic_checkout/edition' )->isExtensionEnabled( 'Ebizmarts_SagePaySuite' ) && in_array( 'checkout_onepage_review', $handles ) ) {
			$update->addHandle( 'onestic_checkout_sagepay_checkout_onepage_review' );
		}
	}

	public function subscribeNewsletter( $observer ) {
		// Bail out if newsletter functionality is disabled in admin
		if ( ! Mage::getStoreConfigFlag( 'checkout/newsletter/enable' ) )
			return;

		// if either admin set in the config to not ask the user if they want to subscribe or if the user has confirmed that they want to subscribe
		if ( ! Mage::getStoreConfigFlag( 'checkout/newsletter/ask_the_user' ) || 1 == Mage::app()->getFrontController()->getRequest()->getParam( 'newsletter_subscribe' ) ) {
			$email = $observer->getEvent()->getOrder()->getCustomerEmail();
			$subscriber = Mage::getModel( 'newsletter/subscriber' )->loadByEmail( $email );

			// If user is already subscribed or unsubscribed, don't do anything except if user has unsubscribed before but has opted to subscribe on checkout page, then re-subscribe.
			if ( $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED &&
				( 1 == Mage::app()->getFrontController()->getRequest()->getParam( 'newsletter_subscribe' ) || $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED ) ) {
				$subscriber->setImportMode( true )->subscribe( $email );
			}
		}
	}

	public function dispatchGiftMessageEventForWhenSagepayTakesOverTheAction( $observer) {
		// Dispatch custom event for hooking in GiftMessage functionality which works on Observer in core
		Mage::dispatchEvent( 'checkout_controller_onepage_save_giftmessage', array( 'request' => $observer->getEvent()->getControllerAction()->getRequest(), 'quote' => Mage::getSingleton('checkout/session')->getQuote() ) );
	}

	public function layoutUpdate( $observer ) {
		if ( Mage::helper( 'onestic_checkout/edition' )->isMageEnterprise() ) {
			$updates = $observer->getEvent()->getUpdates();
			$updates->addChild( 'onestic_checkout_enterprise' )->file = 'onestic_checkout/enterprise.xml';
		}
	}

}