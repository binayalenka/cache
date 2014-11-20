<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE borderfree_countries (id INT(8) NOT NULL AUTO_INCREMENT, country_code char(2) NOT NULL, merchant_id char(20), name char(75) NOT NULL, bill_to BOOLEAN NOT NULL, ship_to BOOLEAN NOT NULL, postal_code BOOLEAN NOT NULL, currency_code char(3) NOT NULL, language_code char(2) NOT NULL, locale char(10) NOT NULL, translation_available BOOLEAN NOT NULL, pricing_model char(10) NOT NULL, PRIMARY KEY (id), INDEX(country_code, merchant_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>
