<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$installer->addAttribute('catalog_product', 'size_chart_url',  array(
			'group' => 'Borderfree',
			'sort_order' => 2,
			'type' => 'int',
			'backend' => '',
			'frontend' => '',
			'label' => 'Size chart URL',
			'note' => '',
			'input' => 'text',
			'class' => '',
			'source' => '',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
			'visible' => true,
			'required' => false,
			'user_defined' => true,
			'default' => '0',
			'visible_on_front' => false,
			'unique' => false,
			'is_configurable' => false,
			'used_for_promo_rules' => false
		));

$installer->addAttribute('catalog_product', 'is_exclusive',  array(
        'group' => 'Borderfree',
        'sort_order' => 3,
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Is Exclusive',
        'note' => 'Indicates whether the Product is exclusive to your catalog.',
        'input' => 'select',
        'class' => '',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '0',
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => false
));


$installer->endSetup();
?>
