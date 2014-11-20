<?php
$installer = $this;
$installer->startSetup();

$installer->run("DROP TABLE borderfree_cronjob_log;");
$installer->run("CREATE TABLE borderfree_cronjob_log (type char(30) NOT NULL, last_run datetime, PRIMARY KEY (type)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
?>
