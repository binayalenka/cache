<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute('catalog_product', 'fws_common_name',  array(
		'group' => 'Borderfree',
		'sort_order' => 34,
		'type' => 'int',
		'backend' => '',
		'frontend' => '',
		'label' => 'Fish and Wildlife Common Name',
		'note' => '',
		'input' => 'text',
		'class' => '',
		'source' => '',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
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
