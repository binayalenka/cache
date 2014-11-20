<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$installer->addAttribute('catalog_product', 'borderfree_restricted',  array(
			'group' => 'Borderfree',
			'sort_order' => 1,
			'type' => 'int',
			'backend' => '',
			'frontend' => '',
			'label' => 'Borderfree Restricted',
			'note' => 'This product will be restricted from the Borderfree catalog.',
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

/*$attributeId = $installer->getAttributeId($entityTypeId, 'borderfree_restricted');
$installer->run("
INSERT INTO `{$installer->getTable('catalog_product_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '0'
        FROM `{$installer->getTable('catalog_product_entity')}`;
");*/
$installer->endSetup();
?>
