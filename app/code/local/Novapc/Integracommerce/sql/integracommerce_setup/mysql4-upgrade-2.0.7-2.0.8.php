<?php
/**
 * PHP version 5
 * Novapc Integracommerce
 *
 * @category  Magento
 * @package   Novapc_Integracommerce
 * @author    Novapc <novapc@novapc.com.br>
 * @copyright 2017 Integracommerce
 * @license   https://opensource.org/licenses/osl-3.0.php PHP License 3.0
 * @version   GIT: 1.0
 * @link      https://github.com/integracommerce/modulo-magento
 */

$installer = $this;
$installer->startSetup();

$tablePrefix = Mage::getConfig()->getTablePrefix();
if (!empty($tablePrefix)) {
    $queueTable = $tablePrefix . 'npcintegra_product_queue';
} else {
    $queueTable = 'npcintegra_product_queue';
}

try {
    if (!$installer->getConnection()->tableColumnExists($queueTable, 'requested_times')) {
        $installer->run("ALTER TABLE `" . $queueTable . "` ADD `requested_times` int(11) NOT NULL DEFAULT 0;");
    }
} catch (Exception $e) {
    Mage::log($e->getMessage(), null, 'Integracommerce_InstallError.log');
}

$installer->endSetup();