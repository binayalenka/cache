<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE borderfree_localization_cache (id int(8) NOT NULL AUTO_INCREMENT, data_type char(30) NOT NULL, merchant_id char(10) NOT NULL, version int(8) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>