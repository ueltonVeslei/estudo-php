<?php
    /**
     * Uecommerce
     *
     * NOTICE OF LICENSE
     *
     * This source file is subject to the Uecommerce EULA.
     * It is also available through the world-wide-web at this URL:
     * http://www.uecommerce.com.br/
     *
     * DISCLAIMER
     *
     * Do not edit or add to this file if you wish to upgrade the extension
     * to newer versions in the future. If you wish to customize the extension
     * for your needs please refer to http://www.uecommerce.com.br/ for more information
     *
     * @category   Uecommerce
     * @package    Uecommerce_Mundipagg
     * @copyright  Copyright (c) 2012 Uecommerce (http://www.uecommerce.com.br/)
     * @license    http://www.uecommerce.com.br/
     */

    /**
     * Mundipagg Payment module
     *
     * @category   Uecommerce
     * @package    Uecommerce_Mundipagg
     * @author     Uecommerce Dev Team
     */

class Uecommerce_Mundipagg_Model_Source_CctypeProductInstallments
{
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => 'Padrões'),
            array('value' => 'VI', 'label' => 'Visa'),
            array('value' => 'MC', 'label' => 'Mastercard'),
            array('value' => 'AE', 'label' => 'Amex'),
            array('value' => 'DI', 'label' => 'Diners'),
            array('value' => 'EL', 'label' => 'Elo'),
            array('value' => 'HI', 'label' => 'Hipercard'),
        );
    }
}
