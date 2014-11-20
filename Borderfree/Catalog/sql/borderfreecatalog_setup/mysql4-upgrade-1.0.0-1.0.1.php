<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_category');
$installer->addAttributeSet($entityTypeId, "Borderfree", $sortOrder = null);
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$installer->addAttributeGroup($entityTypeId, $attributeSetId, "Borderfree", 3);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, "Borderfree");

$installer->addAttribute('catalog_category', 'borderfree_restricted',  array(
    'type'     => 'int',
    'label'    => 'Borderfree Restricted',
    'input'    => 'select',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
	'default'			=> 0,
	'source'			=> "eav/entity_attribute_source_boolean",
	'note'				=> 'This category and all children will be restricted from the Borderfree catalog.'
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'borderfree_restricted',
    '1'
);
/*$attributeId = $installer->getAttributeId($entityTypeId, 'borderfree_restricted');
$installer->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '0'
        FROM `{$installer->getTable('catalog_category_entity')}`;
");*/
$installer->endSetup();
?>
