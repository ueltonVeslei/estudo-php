<?php
/**
 * Onestic
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Integrator
 * @package    Onestic_Overcoupom
 * @copyright  Copyright (c) 2018 Onestic
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 

class Onestic_Overcoupom_Model_Resource_Coupon_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
   public function _construct()
   {
       $this->_init('onestic_overcoupom/coupon');
   }
}