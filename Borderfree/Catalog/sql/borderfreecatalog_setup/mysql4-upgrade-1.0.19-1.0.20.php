<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE borderfree_catalog_export_stores (store_id int(8) NOT NULL, status int(1) NOT NULL, start_time datetime, batch_id char(12) NOT NULL, filenum int(8) NOT NULL, request_id int(8) NOT NULL, type char(15) NOT NULL, PRIMARY KEY (store_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->run("CREATE TABLE borderfree_catalog_export_products (product_id int(8) NOT NULL, status int(1) NOT NULL, PRIMARY KEY (product_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>
