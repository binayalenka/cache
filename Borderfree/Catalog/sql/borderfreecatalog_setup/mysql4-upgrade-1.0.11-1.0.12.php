<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$installer->addAttribute('catalog_product', 'box_width',  array(
			'group' => 'Borderfree',
			'sort_order' => 23,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'frontend_class' => "validate-number",
			'label' => 'Box Width',
			'note' => '',
			'input' => 'text',
			'class' => '',
			'source' => '',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'visible' => true,
			'required' => false,
			'user_defined' => true,
			'default' => '',
			'visible_on_front' => false,
			'unique' => false,
			'is_configurable' => false,
			'used_for_promo_rules' => false,
			'apply_to' => '',
			'searchable' => false,
			'filterable' => 0,
			'comparable' => false,
			'visible_in_advanced_search' => false,
			'used_for_price_rules' => false,
			'filterable_in_search' => false
		));

$installer->addAttribute('catalog_product', 'box_length',  array(
			'group' => 'Borderfree',
			'sort_order' => 24,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'frontend_class' => "validate-number",
			'label' => 'Box Length',
			'note' => '',
			'input' => 'text',
			'class' => '',
			'source' => '',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'visible' => true,
			'required' => false,
			'user_defined' => true,
			'default' => '',
			'visible_on_front' => false,
			'unique' => false,
			'is_configurable' => false,
			'used_for_promo_rules' => false,
			'apply_to' => '',
			'searchable' => false,
			'filterable' => 0,
			'comparable' => false,
			'visible_in_advanced_search' => false,
			'used_for_price_rules' => false,
			'filterable_in_search' => false
		));

$installer->addAttribute('catalog_product', 'box_height',  array(
		'group' => 'Borderfree',
		'sort_order' => 25,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'frontend_class' => "validate-number",
		'label' => 'Box Height',
		'note' => '',
		'input' => 'text',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible' => true,
		'required' => false,
		'user_defined' => true,
		'default' => '',
		'visible_on_front' => false,
		'unique' => false,
		'is_configurable' => false,
		'used_for_promo_rules' => false,
		'apply_to' => '',
		'searchable' => false,
		'filterable' => 0,
		'comparable' => false,
		'visible_in_advanced_search' => false,
		'used_for_price_rules' => false,
		'filterable_in_search' => false
));

$installer->addAttribute('catalog_product', 'product_width',  array(
			'group' => 'Borderfree',
			'sort_order' => 26,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'frontend_class' => "validate-number",
			'label' => 'Product Width',
			'note' => '',
			'input' => 'text',
			'class' => '',
			'source' => '',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'visible' => true,
			'required' => false,
			'user_defined' => true,
			'default' => '',
			'visible_on_front' => false,
			'unique' => false,
			'is_configurable' => false,
			'used_for_promo_rules' => false,
			'apply_to' => '',
			'searchable' => false,
			'filterable' => 0,
			'comparable' => false,
			'visible_in_advanced_search' => false,
			'used_for_price_rules' => false,
			'filterable_in_search' => false
		));

$installer->addAttribute('catalog_product', 'product_length',  array(
			'group' => 'Borderfree',
			'sort_order' => 27,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'frontend_class' => "validate-number",
			'label' => 'Product Length',
			'note' => '',
			'input' => 'text',
			'class' => '',
			'source' => '',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'visible' => true,
			'required' => false,
			'user_defined' => true,
			'default' => '',
			'visible_on_front' => false,
			'unique' => false,
			'is_configurable' => false,
			'used_for_promo_rules' => false,
			'apply_to' => '',
			'searchable' => false,
			'filterable' => 0,
			'comparable' => false,
			'visible_in_advanced_search' => false,
			'used_for_price_rules' => false,
			'filterable_in_search' => false
		));

$installer->addAttribute('catalog_product', 'product_height',  array(
		'group' => 'Borderfree',
		'sort_order' => 28,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'frontend_class' => "validate-number",
		'label' => 'Product Height',
		'note' => '',
		'input' => 'text',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible' => true,
		'required' => false,
		'user_defined' => true,
		'default' => '',
		'visible_on_front' => false,
		'unique' => false,
		'is_configurable' => false,
		'used_for_promo_rules' => false,
		'apply_to' => '',
		'searchable' => false,
		'filterable' => 0,
		'comparable' => false,
		'visible_in_advanced_search' => false,
		'used_for_price_rules' => false,
		'filterable_in_search' => false
));

$installer->endSetup();
?>
