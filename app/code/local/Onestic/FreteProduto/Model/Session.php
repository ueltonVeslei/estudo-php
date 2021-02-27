<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Onestic
 * @package    Onestic_FreteProduto
 * @copyright  Copyright (c) 2017 Ecommerce Developer Blog (http://www.onestic.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Session model
 *
 * Using for saving the form data between estimations
 *
 */
class Onestic_FreteProduto_Model_Session extends Mage_Core_Model_Session_Abstract
{
    const NS = 'onestic_freteproduto';

    public function __construct()
    {
        $this->init(self::NS);
    }
}

