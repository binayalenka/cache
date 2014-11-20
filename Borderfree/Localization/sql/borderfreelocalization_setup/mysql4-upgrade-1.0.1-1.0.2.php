<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE borderfree_fx_rates (buyer_currency char(3) NOT NULL, merchant_currency char(3) NOT NULL, fx_rate DOUBLE NOT NULL, PRIMARY KEY (buyer_currency)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
?>
