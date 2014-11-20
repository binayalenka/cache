<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$installer->addAttribute('catalog_product', 'color',  array(
			'group' => 'Borderfree',
			'sort_order' => 29,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'label' => 'Color',
			'note' => '',
			'input' => 'select',
			'class' => '',
			'source' => '',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'visible' => true,
			'required' => false,
			'user_defined' => true,
			'default' => '',
			'visible_on_front' => false,
			'unique' => false,
			'is_configurable' => true,
			'used_for_promo_rules' => false,
			'apply_to' => 'simple',
			'searchable' => true,
			'filterable' => 1,
			'comparable' => true,
			'visible_in_advanced_search' => true,
			'used_for_price_rules' => false,
			'filterable_in_search' => true
		));

$installer->addAttribute('catalog_product', 'size',  array(
			'group' => 'Borderfree',
			'sort_order' => 30,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'label' => 'Size',
			'note' => '',
			'input' => 'select',
			'class' => '',
			'source' => '',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'visible' => true,
			'required' => false,
			'user_defined' => true,
			'default' => '',
			'visible_on_front' => false,
			'unique' => false,
			'is_configurable' => true,
			'used_for_promo_rules' => false,
			'apply_to' => 'simple',
			'searchable' => true,
			'filterable' => 1,
			'comparable' => true,
			'visible_in_advanced_search' => true,
			'used_for_price_rules' => false,
			'filterable_in_search' => true
		));

$installer->endSetup();
?>
