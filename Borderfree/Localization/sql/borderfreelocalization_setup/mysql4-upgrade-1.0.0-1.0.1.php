<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE borderfree_localization_ip (id int(8) NOT NULL AUTO_INCREMENT, country_code char(2) NOT NULL, low INT UNSIGNED NOT NULL, high INT UNSIGNED NOT NULL, PRIMARY KEY (id), INDEX(high), INDEX(low)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>
