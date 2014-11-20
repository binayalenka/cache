<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE borderfree_regions (id int(8) NOT NULL AUTO_INCREMENT, region_code char(2), country_code char(2) NOT NULL, merchant_id char(20), name char(75) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>
