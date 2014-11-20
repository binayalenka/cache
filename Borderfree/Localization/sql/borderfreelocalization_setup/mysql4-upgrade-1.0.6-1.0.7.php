<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE borderfree_fx_rates ADD quote_id int UNSIGNED");
$installer->endSetup();
?>
