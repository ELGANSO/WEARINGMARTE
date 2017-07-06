<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer
    ->getConnection()
    ->newTable($installer->getTable('sociallogin/provider'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Customer Entity Id')
    ->addColumn('provider_code', Varien_Db_Ddl_Table::TYPE_TEXT, 30, array(
        'nullable' => false,
    ), 'Provider Code')
    ->addColumn('provider_id', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable' => false,
    ), 'Provider Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ), 'Created At')
    ->addIndex(
        $installer->getIdxName(
            'sociallogin/provider',
            array('provider_code', 'provider_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('provider_code', 'provider_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('Social Login Providers');

$installer->getConnection()->createTable($table);

$installer->endSetup();
