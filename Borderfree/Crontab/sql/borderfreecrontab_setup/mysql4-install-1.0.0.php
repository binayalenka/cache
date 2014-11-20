<?php
$installer = $this;
$installer->startSetup();

$installer->run("CREATE TABLE borderfree_cronjob_log (id int(8) NOT NULL AUTO_INCREMENT, type char(30) NOT NULL, merchant_id char(20) NOT NULL, last_run datetime, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
?>
