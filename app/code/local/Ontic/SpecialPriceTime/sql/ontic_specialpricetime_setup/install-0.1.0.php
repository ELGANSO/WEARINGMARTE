<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("UPDATE eav_attribute
  SET frontend_input = 'datetime'
  WHERE attribute_code IN ('special_from_date', 'special_to_date')");

$installer->endSetup();