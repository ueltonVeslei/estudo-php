<?php

/**
 * Magaya
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@logistico.com so we can send you a copy immediately.
 *
 *
 * @category   Integration
 * @package    Magaya_Logistico
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magaya_Logistico_Model_System_Source_Config_Order_Daterange
{
    public function toOptionArray()
    {
        $ranges = array(
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            '90d' => 'Last 90 Days'
        );
        
        $options = array();
        foreach ($ranges as $code=>$label) {
                  $options[] = array(
                    'value' => $code,
                    'label' => $label
                  );
        }
        
        return $options;
    }
}
