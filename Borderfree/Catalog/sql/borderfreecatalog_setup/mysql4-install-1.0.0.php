<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_category');
$installer->addAttributeSet($entityTypeId, "Borderfree", $sortOrder = null);
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$installer->addAttributeGroup($entityTypeId, $attributeSetId, "Borderfree", 3);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, "Borderfree");

$installer->addAttribute('catalog_category', 'hs_codes',  array(
    'type'     => 'varchar',
    'label'    => 'Harmonized System (HS) codes',
    'input'    => 'text',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
	'default'			=> "",
	'note'				=> 'Comma Separated List'
));

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'hs_codes',
    '1'
);
/*$attributeId = $installer->getAttributeId($entityTypeId, 'hs_codes');
$installer->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_varchar')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, ''
        FROM `{$installer->getTable('catalog_category_entity')}`;
");*/
$installer->endSetup();
?>
