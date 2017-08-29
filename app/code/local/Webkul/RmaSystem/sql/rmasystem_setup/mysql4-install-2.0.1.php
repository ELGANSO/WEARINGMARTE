<?php
$installer = $this;
$installer->startSetup();
$installer->run("");
$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_rma')}` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`order_id` int(11) NOT NULL,
	`group` varchar(255) NOT NULL,
	`increment_id` varchar(255) NOT NULL,
	`customer_id` int(11) NOT NULL,
	`package_condition` int(11) NOT NULL,
	`resolution_type` int(11) NOT NULL,
	`additional_info` text NOT NULL,
	`customer_delivery_status` int(11) NOT NULL,
	`customer_consignment_no` varchar(255) NOT NULL,
	`admin_delivery_status` int(11) NOT NULL,
	`admin_consignment_no` varchar(255) NOT NULL,
	`images` text NOT NULL,
	`shipping_label` int(11) NOT NULL,
	`guest_email` varchar(255) DEFAULT NULL,
	`status` int(11) NOT NULL,
	`created_at` datetime NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_rma_conversation')}` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`rma_id` int(11) NOT NULL,
	`message` text NOT NULL,
	`created_at` datetime NOT NULL,
	`sender` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_rma_items')}` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`rma_id` int(11) NOT NULL,
	`item_id` int(11) NOT NULL,
	`reason_id` int(11) NOT NULL,
	`qty` int(11) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_rma_label')}` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`filename` varchar(255) NOT NULL,
	`price` decimal(10,2) NOT NULL,
	`status` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('wk_rma_reason')}` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`reason` text NOT NULL,
	`status` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();