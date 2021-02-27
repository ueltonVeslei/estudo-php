<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_AdvancedPromotions
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

$installer = $this;
$installer->startSetup();
$sql = "CREATE TABLE IF NOT EXISTS `{$this->getTable('pradvancedpromotions_index')}` (
  `index_id` int(11) NOT NULL,
  `rule_id` int(11) NOT NULL,
  `rule_name` varchar(255) NOT NULL,
  `coupon_code` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_increment_id` varchar(255) NOT NULL,
  `gt` float NOT NULL
) ENGINE=MyISAM;";

$installer->run($sql);

$sql = "ALTER TABLE `{$this->getTable('pradvancedpromotions_index')}`
  ADD PRIMARY KEY (`index_id`),
  ADD KEY `rule_id` (`rule_id`),
  ADD KEY `rule_name` (`rule_name`),
  ADD KEY `coupon_code` (`coupon_code`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_increment_id` (`order_increment_id`),
  ADD KEY `gt` (`gt`);";
$installer->run($sql);

$sql = "ALTER TABLE `{$this->getTable('pradvancedpromotions_index')}`
  MODIFY `index_id` int(11) NOT NULL AUTO_INCREMENT;";
$installer->run($sql);




$installer->endSetup();