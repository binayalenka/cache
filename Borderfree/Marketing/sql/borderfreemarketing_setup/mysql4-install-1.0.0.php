<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE borderfree_marketing (email char(254) NOT NULL, 
		first_name char(100), 
		middle_initials char(1), 
		last_name char(100),
		address1 char(35),
		address2 char(35),
		address3 char(35),
		city char(70),
		region char(20),
		postal_code char(12),
		country char(2),
		primary_phone char(20),
		scondary_phone char(20),
		PRIMARY KEY (email)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>
