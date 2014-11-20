<?php

$installer = $this;
$installer->startSetup();


$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'borderfree_order_id', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Borderfree Order ID',
    	'length'  => '100'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote'), 'borderfree_order_id', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'comment' => 'Borderfree Order ID',
    	'length'  => '100'
    ));

$installer->endSetup();