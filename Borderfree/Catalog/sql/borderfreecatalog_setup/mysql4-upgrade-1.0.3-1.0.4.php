<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$installer->addAttribute('catalog_product', 'manufacturer',  array(
			'group' => 'Borderfree',
			'sort_order' => 4,
			'type' => 'varchar',
			'backend' => '',
			'frontend' => '',
			'label' => 'Manufacturer',
			'note' => '',
			'input' => 'select',
			'class' => '',
			'source' => '',
			'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
			'visible' => true,
			'required' => false,
			'user_defined' => true,
			'default' => '',
			'visible_on_front' => true,
			'unique' => false,
			'is_configurable' => false,
			'used_for_promo_rules' => true,
			'apply_to' => '',
			'searchable' => true,
			'filterable' => 1,
			'comparable' => true,
			'visible_in_advanced_search' => true,
			'used_for_price_rules' => true,
			'filterable_in_search' => true
		));

$installer->addAttribute('catalog_product', 'brand',  array(
		'group' => 'Borderfree',
		'sort_order' => 5,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'label' => 'Brand',
		'note' => '',
		'input' => 'select',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
		'visible' => true,
		'required' => false,
		'user_defined' => true,
		'default' => '',
		'visible_on_front' => true,
		'unique' => false,
		'is_configurable' => false,
		'used_for_promo_rules' => true,
		'apply_to' => '',
		'searchable' => true,
		'filterable' => 1,
		'comparable' => true,
		'visible_in_advanced_search' => true,
		'used_for_price_rules' => true,
		'filterable_in_search' => true
));

$installer->addAttribute('catalog_product', 'collection',  array(
		'group' => 'Borderfree',
		'sort_order' => 6,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'label' => 'Collection',
		'note' => '',
		'input' => 'select',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
		'visible' => true,
		'required' => false,
		'user_defined' => true,
		'default' => '',
		'visible_on_front' => true,
		'unique' => false,
		'is_configurable' => false,
		'used_for_promo_rules' => true,
		'apply_to' => '',
		'searchable' => true,
		'filterable' => 1,
		'comparable' => true,
		'visible_in_advanced_search' => true,
		'used_for_price_rules' => true,
		'filterable_in_search' => true
));

$installer->addAttribute('catalog_product', 'gender',  array(
		'group' => 'Borderfree',
		'sort_order' => 7,
		'type' => 'varchar',
		'backend' => '',
		'frontend' => '',
		'label' => 'Gender',
		'note' => '',
		'input' => 'select',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
		'visible' => true,
		'required' => false,
		'user_defined' => true,
		'default' => '',
		'visible_on_front' => true,
		'unique' => false,
		'is_configurable' => false,
		'used_for_promo_rules' => true,
		'apply_to' => '',
		'searchable' => true,
		'filterable' => 1,
		'comparable' => true,
		'visible_in_advanced_search' => true,
		'used_for_price_rules' => true,
		'option' => array ( 
				'value' => array(
					'MALE' => array('Male'), 
					'FEMALE' => array('Female'),
					'UNISEX' => array('Unisex'),
				) 
			),
		'filterable_in_search' => true
));

$installer->addAttribute('catalog_product', 'age',  array(
		'group' => 'Borderfree',
		'sort_order' => 8,
		'type' => 'varchar',
		'backend' => 'eav/entity_attribute_backend_array',
		'frontend' => '',
		'label' => 'Age',
		'note' => '',
		'input' => 'multiselect',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
		'visible' => true,
		'required' => false,
		'user_defined' => true,
		'default' => '',
		'visible_on_front' => true,
		'unique' => false,
		'is_configurable' => false,
		'used_for_promo_rules' => true,
		'apply_to' => '',
		'searchable' => true,
		'filterable' => 1,
		'comparable' => true,
		'visible_in_advanced_search' => true,
		'used_for_price_rules' => true,
		'option' => array ( 
				'value' => array(
					'NEWBORN' => array('Newborn'), 
					'INFANT' => array('Infant'),
					'TODDLER' => array('Toddler'),
					'CHILD' => array('Child'), 
					'JUNIOR' => array('Junior'),
					'TEEN' => array('Teen'),
					'ADULT' => array('Adult'),
				) 
			),
		'filterable_in_search' => true
));

$installer->addAttribute('catalog_product', 'season',  array(
		'group' => 'Borderfree',
		'sort_order' => 9,
		'type' => 'varchar',
		'backend' => 'eav/entity_attribute_backend_array',
		'frontend' => '',
		'label' => 'Season',
		'note' => '',
		'input' => 'multiselect',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
		'visible' => true,
		'required' => false,
		'user_defined' => true,
		'default' => '',
		'visible_on_front' => true,
		'unique' => false,
		'is_configurable' => false,
		'used_for_promo_rules' => true,
		'apply_to' => '',
		'searchable' => true,
		'filterable' => 1,
		'comparable' => true,
		'visible_in_advanced_search' => true,
		'used_for_price_rules' => true,
		'option' => array ( 
				'value' => array(
					'SPRING' => array('Spring'), 
					'SUMMER' => array('Summer'),
					'FALL' => array('Fall'),
					'WINTER' => array('Winter'), 
				) 
			),
		'filterable_in_search' => true
));

$installer->endSetup();
?>
