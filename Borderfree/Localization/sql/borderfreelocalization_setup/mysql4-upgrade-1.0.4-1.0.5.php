<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE borderfree_currency (id int(8) NOT NULL AUTO_INCREMENT, currency_code char(3), merchant_id char(20), name char(50), round_method tinyint(1), symbol char(10) NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->run("CREATE TABLE borderfree_payment (id int(8) NOT NULL AUTO_INCREMENT, currency_code char(3), merchant_id char(20), method char(20) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>
