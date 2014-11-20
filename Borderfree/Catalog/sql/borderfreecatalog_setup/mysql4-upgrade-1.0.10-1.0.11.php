<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE borderfree_catalog_export_log (id int(8) NOT NULL AUTO_INCREMENT, type char(15) NOT NULL, merchant_id char(20) NOT NULL, start_time datetime, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>
