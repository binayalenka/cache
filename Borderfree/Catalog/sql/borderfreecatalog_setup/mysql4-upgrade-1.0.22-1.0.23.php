<?php
$installer = $this;
$installer->startSetup();
$installer->run("TRUNCATE TABLE borderfree_catalog_export_stores;");
$installer->run("TRUNCATE TABLE borderfree_catalog_export_products;");
$installer->endSetup();
?>
