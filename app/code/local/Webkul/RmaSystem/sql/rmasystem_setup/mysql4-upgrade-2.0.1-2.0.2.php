<?php
$installer = $this;
$installer->startSetup();
$installer->run("");
$installer->run("
ALTER TABLE `{$this->getTable('wk_rma_items')}` ADD requested_product_id INT(10) UNSIGNED;
ALTER TABLE `{$this->getTable('wk_rma_items')}` ADD requested_product_name VARCHAR(255);
ALTER TABLE `{$this->getTable('wk_rma_items')}` ADD requested_product_size VARCHAR(255);");
$installer->endSetup();