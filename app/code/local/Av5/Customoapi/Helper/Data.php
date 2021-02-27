<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Sale (Orders)
 * @package    Av5_OrderComment
 * @copyright  Copyright (c) 2015 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Av5_Customoapi_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function getDemonstrativoVidalink($order) {
		$demonstrativoVidalink = null;
        foreach ($order->getStatusHistoryCollection() as $status) {
            if (strpos($status->getComment(), 'DEMONSTRATIVO VIDALINK ') !== false) {
                $demonstrativoVidalink = $status->getComment();
                break;
            }
        }

        return $demonstrativoVidalink;
	}
}