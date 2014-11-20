<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute('catalog_product', 'color',  array(
		'group' => 'Borderfree',
		'sort_order' => 19,
		'type' => 'int',
		'backend' => '',
		'frontend' => '',
		'label' => 'Color',
		'note' => '',
		'input' => 'select',
		'class' => '',
		'source' => 'eav/entity_attribute_source_table',
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
