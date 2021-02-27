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

    $installer = $this;

    $installer->startSetup();

    Mage::getConfig()->saveConfig('payment/mundipagg_standard/apiUrlStaging', 'https://stagingv2.mundipaggone.com/Sale/');
    Mage::getConfig()->saveConfig('payment/mundipagg_standard/apiUrlProduction', 'https://transactionv2.mundipaggone.com/Sale/');
    Mage::getConfig()->reinit();
    Mage::getConfig()->cleanCache();
    Mage::app()->reinitStores();

    $installer->endSetup();
