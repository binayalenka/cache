<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$installer->addAttribute('catalog_product', 'care',  array(
			'group' => 'Borderfree',
			'sort_order' => 17,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'label' => 'Care Instructions',
			'note' => '',
			'input' => 'textarea',
			'class' => '',
			'source' => '',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
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
			'filterable_in_search' => false,
			'is_html_allowed_on_front' => true,
			"wysiwyg_enabled" => true
	));

$installer->addAttribute('catalog_product', 'contents',  array(
		'group' => 'Borderfree',
		'sort_order' => 18,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'label' => 'Contents and/or Materials',
		'note' => '(e.g., 60% cotton, 40% rayon). Use commas to separate values in this field.',
		'input' => 'textarea',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
		'visible' => true,
		'required' => false,
		'user_defined' => true,
		'default' => '',
		'visible_on_front' => false,
		'unique' => false,
		'is_configurable' => false,
		'used_for_promo_rules' => false,
		'apply_to' => '',
		'searchable' => true,
		'filterable' => 0,
		'is_html_allowed_on_front' => true,
		'comparable' => false,
		'visible_in_advanced_search' => false,
		'used_for_price_rules' => false,
		'filterable_in_search' => false,
		"wysiwyg_enabled" => true
));

$installer->addAttribute('catalog_product', 'extra_shipping',  array(
		'group' => 'Borderfree',
		'sort_order' => 34,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'frontend_class' => 'validate-number',
		'label' => 'Additional Shipping Charge',
		'note' => '',
		'input' => 'text',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
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
		'is_html_allowed_on_front' => false,
		'comparable' => false,
		'visible_in_advanced_search' => false,
		'used_for_price_rules' => false,
		'filterable_in_search' => false,
		"wysiwyg_enabled" => false
));

$installer->endSetup();
?>
