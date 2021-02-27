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

class Onestic_Awesomecheckout_Block_Form_Stripe extends Radweb_Stripe_Block_Form
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('onestic/checkout/stripe/form.phtml');
    }

}

?>