<?php
$installer = $this;
$installer->startSetup();
$installer->run("");
$installer->run("
ALTER TABLE `{$this->getTable('wk_rma')}` ADD pickup_region VARCHAR(255);
");
$installer->endSetup();