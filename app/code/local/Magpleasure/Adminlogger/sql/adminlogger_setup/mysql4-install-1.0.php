<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Adminlogger
 * @copyright  Copyright (c) 2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */
$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('mp_adminlogger_details_int')}`;
DROP TABLE IF EXISTS `{$this->getTable('mp_adminlogger_details_decimal')}`;
DROP TABLE IF EXISTS `{$this->getTable('mp_adminlogger_details_varchar')}`;
DROP TABLE IF EXISTS `{$this->getTable('mp_adminlogger_details_text')}`;

DROP TABLE IF EXISTS `{$this->getTable('mp_adminlogger_details')}`;
DROP TABLE IF EXISTS `{$this->getTable('mp_adminlogger_log')}`;


CREATE TABLE IF NOT EXISTS `{$this->getTable('mp_adminlogger_log')}` (
   `log_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT ,
   `action_time` timestamp NOT NULL ,
   `action_group` smallint(5) UNSIGNED NOT NULL ,
   `user_id` int(10) UNSIGNED NULL ,
   `action_type` smallint(5) NOT NULL ,
   `remote_addr` bigint(20) NOT NULL COMMENT 'Remote Address',
   `entity_id` bigint(20),
   `store_id` smallint(5) NOT NULL ,
   `website_id` smallint(5) NULL ,
   PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table IF NOT EXISTS `{$this->getTable('mp_adminlogger_details')}`(
   `detail_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT ,
   `log_id` bigint UNSIGNED NOT NULL,
   `attribute_code` varchar(255) NOT NULL,
  PRIMARY KEY (`detail_id`),
  KEY `MP_ADMINLOGGER_DETAILS_LOG_ID` (`log_id`),
  CONSTRAINT `MP_ADMINLOGGER_DETAILS_LOG_ID` FOREIGN KEY (`log_id`) REFERENCES `{$this->getTable('mp_adminlogger_log')}` (`log_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table IF NOT EXISTS `{$this->getTable('mp_adminlogger_details_int')}`(
   `value_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT ,
   `detail_id` bigint UNSIGNED NOT NULL,
   `direction` smallint(1) NOT NULL,
   `value` bigint NOT NULL,
  PRIMARY KEY (`value_id`),
  KEY `MP_ADMINLOGGER_VALUE_INT_DETAILS` (`detail_id`),
  CONSTRAINT `MP_ADMINLOGGER_VALUE_INT_DETAILS` FOREIGN KEY (`detail_id`) REFERENCES `{$this->getTable('mp_adminlogger_details')}` (`detail_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table IF NOT EXISTS `{$this->getTable('mp_adminlogger_details_decimal')}`(
   `value_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT ,
   `detail_id` bigint UNSIGNED NOT NULL,
   `direction` smallint(1) NOT NULL,
   `value` decimal(12,4) NOT NULL,
  PRIMARY KEY (`value_id`),
  KEY `MP_ADMINLOGGER_VALUE_DECIMAL_DETAILS` (`detail_id`),
  CONSTRAINT `MP_ADMINLOGGER_VALUE_DECIMAL_DETAILS` FOREIGN KEY (`detail_id`) REFERENCES `{$this->getTable('mp_adminlogger_details')}` (`detail_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table IF NOT EXISTS `{$this->getTable('mp_adminlogger_details_varchar')}`(
   `value_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT ,
   `detail_id` bigint UNSIGNED NOT NULL,
   `direction` smallint(1) NOT NULL,
   `value` varchar(255) NOT NULL,
  PRIMARY KEY (`value_id`),
  KEY `MP_ADMINLOGGER_VALUE_VARCHAR_DETAILS` (`detail_id`),
  CONSTRAINT `MP_ADMINLOGGER_VALUE_VARCHAR_DETAILS` FOREIGN KEY (`detail_id`) REFERENCES `{$this->getTable('mp_adminlogger_details')}` (`detail_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table IF NOT EXISTS `{$this->getTable('mp_adminlogger_details_text')}`(
   `value_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT ,
   `detail_id` bigint UNSIGNED NOT NULL,
   `direction` smallint(1) NOT NULL,
   `value` text NOT NULL,
  PRIMARY KEY (`value_id`),
  KEY `MP_ADMINLOGGER_VALUE_TEXT_DETAILS` (`detail_id`),
  CONSTRAINT `MP_ADMINLOGGER_VALUE_TEXT_DETAILS` FOREIGN KEY (`detail_id`) REFERENCES `{$this->getTable('mp_adminlogger_details')}` (`detail_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 