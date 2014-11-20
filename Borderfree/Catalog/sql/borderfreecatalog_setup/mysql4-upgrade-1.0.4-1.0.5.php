<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$installer->addAttribute('catalog_product', 'gtin',  array(
			'group' => 'Borderfree',
			'sort_order' => 10,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'label' => 'Global Trade Item Number',
			'note' => '',
			'input' => 'text',
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
			'filterable' => 1,
			'comparable' => false,
			'visible_in_advanced_search' => false,
			'used_for_price_rules' => false,
			'filterable_in_search' => false
		));

$installer->addAttribute('catalog_product', 'upc',  array(
			'group' => 'Borderfree',
			'sort_order' => 11,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'label' => 'Universal Product Code',
			'note' => '',
			'input' => 'text',
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
			'filterable' => 1,
			'comparable' => false,
			'visible_in_advanced_search' => false,
			'used_for_price_rules' => false,
			'filterable_in_search' => false
		));

$installer->addAttribute('catalog_product', 'ean',  array(
		'group' => 'Borderfree',
		'sort_order' => 12,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'label' => 'European Article Number',
		'note' => '',
		'input' => 'text',
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
		'filterable' => 1,
		'comparable' => false,
		'visible_in_advanced_search' => false,
		'used_for_price_rules' => false,
		'filterable_in_search' => false
));

$installer->addAttribute('catalog_product', 'apn',  array(
		'group' => 'Borderfree',
		'sort_order' => 13,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'label' => 'Australian Product Number',
		'note' => '',
		'input' => 'text',
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
		'filterable' => 1,
		'comparable' => false,
		'visible_in_advanced_search' => false,
		'used_for_price_rules' => false,
		'filterable_in_search' => false
));

$installer->addAttribute('catalog_product', 'jan',  array(
		'group' => 'Borderfree',
		'sort_order' => 14,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'label' => 'Japanese Article Number',
		'note' => '',
		'input' => 'text',
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
		'filterable' => 1,
		'comparable' => false,
		'visible_in_advanced_search' => false,
		'used_for_price_rules' => false,
		'filterable_in_search' => false
));

$installer->addAttribute('catalog_product', 'eccn',  array(
		'group' => 'Borderfree',
		'sort_order' => 15,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'label' => 'Export Control Classification Number',
		'note' => '',
		'input' => 'text',
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
		'filterable' => 1,
		'comparable' => false,
		'visible_in_advanced_search' => false,
		'used_for_price_rules' => false,
		'filterable_in_search' => false
));

$installer->addAttribute('catalog_product', 'mpn',  array(
		'group' => 'Borderfree',
		'sort_order' => 16,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'label' => 'Manufacturer Part Number',
		'note' => '',
		'input' => 'text',
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
		'filterable' => 1,
		'comparable' => false,
		'visible_in_advanced_search' => false,
		'used_for_price_rules' => false,
		'filterable_in_search' => false
));

$installer->endSetup();
?>
