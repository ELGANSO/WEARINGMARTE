<?php
$installer = $this;
$installer->startSetup();
$installer->run("");
$installer->run("
ALTER TABLE `{$this->getTable('wk_rma')}` ADD pickup_date DATE;
ALTER TABLE `{$this->getTable('wk_rma')}` ADD pickup_address VARCHAR(255);
ALTER TABLE `{$this->getTable('wk_rma')}` ADD pickup_number VARCHAR(255);
ALTER TABLE `{$this->getTable('wk_rma')}` ADD pickup_postcode VARCHAR(255);
ALTER TABLE `{$this->getTable('wk_rma')}` ADD pickup_city VARCHAR(255);
ALTER TABLE `{$this->getTable('wk_rma')}` ADD pickup_phone VARCHAR(255);
");
$installer->endSetup();