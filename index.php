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
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (version_compare(phpversion(), '5.2.0', '<')===true) {
    echo  '<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0; font-size:1.7em; font-weight:normal; text-transform:none; text-align:left; color:#2f2f2f;">
            Whoops, it looks like you have an invalid PHP version.</h3></div><p>Magento supports PHP 5.2.0 or newer.
            <a href="http://www.magentocommerce.com/install" target="">Find out</a> how to install</a>
            Magento using PHP-CGI as a work-around.</p></div>';
    exit;
}

/**
 * Error reporting
 */
error_reporting(E_ALL | E_STRICT);

/**
 * Compilation includes configuration file
 */
define('MAGENTO_ROOT', getcwd());

$compilerConfig = MAGENTO_ROOT . '/includes/config.php';
if (file_exists($compilerConfig)) {
    include $compilerConfig;
}

$mageFilename = MAGENTO_ROOT . '/app/Mage.php';
$maintenanceFile = 'maintenance.flag';

if (!file_exists($mageFilename)) {
    if (is_dir('downloader')) {
        header("Location: downloader");
    } else {
        echo $mageFilename." was not found";
    }
    exit;
}

if (file_exists($maintenanceFile)) {
    #include_once dirname(__FILE__) . '/errors/503.php';
    include_once dirname(__FILE__) . '/index-maintenence.html';
    exit;
}

require_once $mageFilename;

#Varien_Profiler::enable();

if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
    Mage::setIsDeveloperMode(true);
}

ini_set('display_errors', 1);
umask(0);


/* Store or website code */
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';

/* Run store or run website */
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';

if($_SERVER['HTTP_HOST']=='www.accuchekdelivery.com.br')
{
    $mageRunCode = 2;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='accucheck.catalog.farmadelivery.com.br')
{
    $mageRunCode = 2;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='usafarmadelivery.com')
{
    $mageRunCode = 20;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='www.usafarmadelivery.com')
{
    $mageRunCode = 20;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='japan.farmadelivery.com.br')
{
    $mageRunCode = 3;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='japan.catalog.farmadelivery.com.br')
{
    $mageRunCode = 3;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='www.genericosdelivery.com.br')
{
    $mageRunCode = 4;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='genericosdelivery.com.br')
{
    $mageRunCode = 4;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='genericos.catalog.farmadelivery.com.br')
{
    $mageRunCode = 4;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='clubededescontos.farmadelivery.com.br')
{
    $mageRunCode = 8;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='descontos.catalog.farmadelivery.com.br')
{
    $mageRunCode = 8;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='santander.farmadelivery.com.br')
{
    $mageRunCode = 10;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='santander.catalog.farmadelivery.com.br')
{
    $mageRunCode = 10;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='itaucard.farmadelivery.com.br')
{
    $mageRunCode = 24;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='credicard.farmadelivery.com.br')
{
    $mageRunCode = 25;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='hipercard.farmadelivery.com.br')
{
    $mageRunCode = 27;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='iupp.farmadelivery.com.br')
{
    $mageRunCode = 28;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='portoseguro.farmadelivery.com.br')
{
    $mageRunCode = 12;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='portoseguro.catalog.farmadelivery.com.br')
{
    $mageRunCode = 12;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='solufarma.com.br')
{
    $mageRunCode = 21;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='www.solufarma.com.br')
{
    $mageRunCode = 21;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='bomparatodos.net')
{
    $mageRunCode = 22;
    $mageRunType = 'website';
    ini_set('session.cookie_domain','.bomparatodos.net');
}
else if ($_SERVER['HTTP_HOST']=='www.bomparatodos.net')
{
    $mageRunCode = 22;
    $mageRunType = 'website';
    ini_set('session.cookie_domain','.bomparatodos.net');
}
/*
else if ($_SERVER['HTTP_HOST']=='vcdelivery.com.br')
{
    $mageRunCode = 23;
    $mageRunType = 'website';
    ini_set('session.cookie_domain','.vcdelivery.com.br');
}
else if ($_SERVER['HTTP_HOST']=='www.vcdelivery.com.br')
{
    $mageRunCode = 23;
    $mageRunType = 'website';
    ini_set('session.cookie_domain','.vcdelivery.com.br');
}
*/

else if ($_SERVER['HTTP_HOST']=='vcdelivery.com.br')
{
    $mageRunCode = 23;
    $mageRunType = 'website';
    ini_set('session.cookie_domain','.vcdelivery.com.br');
}
else if ($_SERVER['HTTP_HOST']=='www.vcdelivery.com.br')
{
    $mageRunCode = 23;
    $mageRunType = 'website';
    ini_set('session.cookie_domain','.vcdelivery.com.br');
}

else if (strpos($_SERVER['REQUEST_URI'], 'goiania') === 1)
{
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 8);
    $mageRunCode = 13;
    $mageRunType = 'website';
}
else if (strpos($_SERVER['REQUEST_URI'], 'viavarejo') === 1)
{
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 10);
    $mageRunCode = 19;
    $mageRunType = 'website';
}
else if ($_SERVER['HTTP_HOST']=='poupafarma.farmadelivery.com.br')
{
    $mageRunCode = 29;
    $mageRunType = 'website';
}
else
{
    $mageRunCode = 1;
    $mageRunType = 'website';
}

Mage::run($mageRunCode, $mageRunType);
