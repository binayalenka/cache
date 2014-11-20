<?php
$installer = $this;
$installer->startSetup();
$installer->run("DROP TABLE borderfree_lcp_rules;");
$installer->run("DROP TABLE borderfree_rounding_rules;");
$installer->run("CREATE TABLE borderfree_lcp_rules (id int(8) NOT NULL AUTO_INCREMENT, rule_id int(8) NOT NULL, country_code char(2) NOT NULL, merchant_id char(20) NOT NULL, name char(50), description text(1024), multiplier DECIMAL(3,2) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->run("CREATE TABLE borderfree_rounding_rules (id int(8) NOT NULL AUTO_INCREMENT, rule_id int(8) NOT NULL, country_code char(2) NOT NULL, merchant_id char(20) NOT NULL, name char(50), description text(1024), amount DECIMAL(3,2) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>
