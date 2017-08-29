<?php
$installer = $this;
$installer->startSetup();
$installer->run("");
$installer->run("
TRUNCATE TABLE `{$this->getTable('wk_rma_reason')}`;
INSERT INTO `{$this->getTable('wk_rma_reason')}`(reason, status) VALUES('Default', 1);
");
$installer->endSetup();